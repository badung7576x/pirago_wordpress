<?php
/**
 * Astra Portfolio
 *
 * @package Astra Portfolio
 * @since 1.0.0
 */

if ( ! class_exists( 'Astra_Portfolio_Page' ) ) :

	/**
	 * Astra_Portfolio_Page
	 *
	 * @since 1.0.0
	 */
	class Astra_Portfolio_Page {

		/**
		 * View all actions
		 *
		 * @since 1.0.0
		 * @var array $view_actions
		 */
		public static $view_actions = array();

		/**
		 * Menu page title
		 *
		 * @since 1.0.0
		 * @var array $menu_page_title
		 */
		public static $menu_page_title = 'WP Portfolio';

		/**
		 * Plugin slug
		 *
		 * @since 1.0.0
		 * @var array $plugin_slug
		 */
		public static $plugin_slug = 'astra-portfolio';

		/**
		 * Default Menu position
		 *
		 * @since 1.0.0
		 * @var array $default_menu_position
		 */
		public static $default_menu_position = 'edit.php?post_type=astra-portfolio';

		/**
		 * Parent Page Slug
		 *
		 * @since 1.0.0
		 * @var array $parent_page_slug
		 */
		public static $parent_page_slug = 'general';

		/**
		 * Current Slug
		 *
		 * @since 1.0.0
		 * @var array $current_slug
		 */
		public static $current_slug = 'general';

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class Instance.
		 * @since 1.0.0
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {

			if ( ! is_admin() ) {
				return;
			}

			add_action( 'after_setup_theme', __CLASS__ . '::init_admin_settings', 102 );
			add_action( 'plugin_action_links_' . ASTRA_PORTFOLIO_BASE, array( $this, 'action_links' ) );
			add_filter( 'admin_url', array( $this, 'admin_url' ), 10, 3 );
			add_action( 'before_delete_post', array( $this, 'delete_remote_id_from_excluded_ids' ) );
			add_action( 'wp_ajax_astra_portfolio_batch_status', array( $this, 'show_batch_status' ) );
		}

		/**
		 * Show the current batch process status
		 *
		 * @since 1.7.0
		 *
		 * @return void
		 */
		public function show_batch_status() {

			$message          = get_option( 'astra-portfolio-batch-process-string', 'Sync' );
			$process_complete = get_option( 'astra-portfolio-batch-process-all-complete', 'no' );

			if ( 'yes' === $process_complete ) {
				wp_send_json_error( $message );
			}

			wp_send_json_success( $message );
		}

		/**
		 * Remove excluded remote post ID to re-import it.
		 *
		 * @since 1.7.0
		 *
		 * @param int $postid Post ID.
		 */
		public function delete_remote_id_from_excluded_ids( $postid = 0 ) {

			$excluded_ids = (array) get_option( 'astra_portfolio_batch_excluded_sites', array() );
			if ( empty( $excluded_ids ) ) {
				return;
			}

			$excluded_remote_id = get_post_meta( $postid, 'astra-remote-post-id', true );
			if ( empty( $excluded_remote_id ) ) {
				return;
			}

			$key = array_search( $excluded_remote_id, $excluded_ids, true );

			if ( false === $key ) {
				return;
			}

			// Unset the remote post ID.
			unset( $excluded_ids[ $key ] );

			// Update excluded post array ids.
			update_option( 'astra_portfolio_batch_excluded_sites', (array) $excluded_ids );
		}

		/**
		 * Filters the admin area URL.
		 *
		 * @since 1.0.2
		 *
		 * @param string   $url     The complete admin area URL including scheme and path.
		 * @param string   $path    Path relative to the admin area URL. Blank string if no path is specified.
		 * @param int|null $blog_id Site ID, or null for the current site.
		 */
		public function admin_url( $url, $path, $blog_id ) {

			if ( 'post-new.php?post_type=astra-portfolio' !== $path ) {
				return $url;
			}

			$url  = get_site_url( $blog_id, 'wp-admin/', 'admin' );
			$path = 'edit.php?post_type=astra-portfolio&page=astra-portfolio-add-new';

			if ( $path && is_string( $path ) ) {
				$url .= ltrim( $path, '/' );
			}

			return $url;
		}

		/**
		 * Admin settings init
		 */
		public static function init_admin_settings() {

			self::$menu_page_title = __( 'Settings', 'astra-portfolio' );

			if ( isset( $_REQUEST['page'] ) && strpos( $_REQUEST['page'], self::$plugin_slug ) !== false ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				// Let extensions hook into saving.
				do_action( 'astra_portfolio_settings_scripts' );

				self::save_settings();
			}

			add_action( 'admin_menu', __CLASS__ . '::add_admin_menu', 99 );
			add_action( 'astra_portfolio_menu_general_action', __CLASS__ . '::general_page' );
			add_action( 'astra_portfolio_menu_style_action', __CLASS__ . '::style_page' );
			add_action( 'astra_portfolio_menu_advanced_action', __CLASS__ . '::advanced_page' );
			add_action( 'init', __CLASS__ . '::process_form', 11 );
			add_action( 'admin_enqueue_scripts', __CLASS__ . '::admin_scripts' );

			// Current user can edit?
			if ( current_user_can( 'edit_posts' ) ) {
				add_action( 'admin_menu', __CLASS__ . '::register' );
				add_filter( 'submenu_file', __CLASS__ . '::submenu_file', 999, 2 );
			}
		}

		/**
		 * Sets the active menu item for the builder admin submenu.
		 *
		 * @since 1.0.2
		 *
		 * @param string $submenu_file  Submenu file.
		 * @param string $parent_file   Parent file.
		 * @return string               Submenu file.
		 */
		public static function submenu_file( $submenu_file, $parent_file ) {
			global $pagenow;

			$screen = get_current_screen();

			if ( isset( $_GET['page'] ) && 'astra-portfolio-add-new' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$submenu_file = 'astra-portfolio-add-new';
			} elseif ( 'post.php' === $pagenow && 'astra-portfolio' === $screen->post_type ) {
				$submenu_file = 'edit.php?post_type=astra-portfolio';
			} elseif ( 'edit-tags.php' === $pagenow && 'astra-portfolio-tags' === $screen->taxonomy ) {
				$submenu_file = 'edit-tags.php?taxonomy=astra-portfolio-tags&post_type=astra-portfolio';
			} elseif ( 'edit-tags.php' === $pagenow && 'astra-portfolio-categories' === $screen->taxonomy ) {
				$submenu_file = 'edit-tags.php?taxonomy=astra-portfolio-categories&post_type=astra-portfolio';
			} elseif ( 'edit-tags.php' === $pagenow && 'astra-portfolio-other-categories' === $screen->taxonomy ) {
				$submenu_file = 'edit-tags.php?taxonomy=astra-portfolio-other-categories&post_type=astra-portfolio';
			}

			return $submenu_file;
		}

		/**
		 * Registers the add new portfolio form admin menu for adding portfolios.
		 *
		 * @since 1.0.2
		 *
		 * @return void
		 */
		public static function register() {
			global $submenu, $_registered_pages;

			$parent        = 'edit.php?post_type=astra-portfolio';
			$tags_url      = 'edit-tags.php?taxonomy=astra-portfolio-tags&post_type=astra-portfolio';
			$cat_url       = 'edit-tags.php?taxonomy=astra-portfolio-categories&post_type=astra-portfolio';
			$other_cat_url = 'edit-tags.php?taxonomy=astra-portfolio-other-categories&post_type=astra-portfolio';
			$add_new_hook  = 'astra-portfolio_page_astra-portfolio-add-new';

			$submenu[ $parent ]     = array(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$submenu[ $parent ][10] = array( __( 'All Portfolio Items', 'astra-portfolio' ), 'edit_posts', $parent ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$submenu[ $parent ][20] = array( __( 'Add New', 'astra-portfolio' ), 'edit_posts', 'astra-portfolio-add-new', '' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$submenu[ $parent ][30] = array( __( 'Categories', 'astra-portfolio' ), 'manage_categories', $cat_url ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$submenu[ $parent ][40] = array( __( 'Other Categories', 'astra-portfolio' ), 'manage_categories', $other_cat_url ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$submenu[ $parent ][50] = array( __( 'Tags', 'astra-portfolio' ), 'manage_categories', $tags_url ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

			add_action( $add_new_hook, __CLASS__ . '::add_new_page' );
			$_registered_pages[ $add_new_hook ] = true; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		/**
		 * Add new page
		 *
		 * @since 1.0.2
		 */
		public static function add_new_page() {
			$types = self::get_portfolio_types();

			require_once ASTRA_PORTFOLIO_DIR . 'includes/add-new-form.php';
		}

		/**
		 * Create the portfolio from add new portfolio form.
		 *
		 * @since 1.0.2
		 *
		 * @return void
		 */
		public static function process_form() {
			$page = isset( $_GET['page'] ) ? $_GET['page'] : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( 'astra-portfolio-add-new' !== $page ) {
				return;
			}

			if ( ! isset( $_POST['astra-portfolio-add-template'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST['astra-portfolio-add-template'], 'astra-portfolio-add-template-nonce' ) ) {
				return;
			}

			$title = sanitize_text_field( $_POST['astra-portfolio-template']['title'] );
			$type  = sanitize_text_field( $_POST['astra-portfolio-template']['type'] );

			// Insert portfolio.
			$post_id = wp_insert_post(
				array(
					'post_title'     => $title,
					'post_type'      => 'astra-portfolio',
					'post_status'    => 'draft',
					'ping_status'    => 'closed',
					'comment_status' => 'closed',
					'meta_input'     => array(
						'astra-portfolio-type' => $type,
					),
				)
			);

			// Redirect to the new portfolio.
			wp_safe_redirect( admin_url( '/post.php?post=' . $post_id . '&action=edit' ) );

			exit;
		}

		/**
		 * Get portfolio type
		 *
		 * @since 1.0.2
		 *
		 * @return array Portfolio types.
		 */
		public static function get_portfolio_types() {

			$all_types = apply_filters(
				'astra_portfolio_add_new_types',
				array(
					array(
						'key'   => 'iframe',
						'label' => __( 'Website', 'astra-portfolio' ),
					),
					array(
						'key'   => 'image',
						'label' => __( 'Image', 'astra-portfolio' ),
					),
					array(
						'key'   => 'video',
						'label' => __( 'Video', 'astra-portfolio' ),
					),
					array(
						'key'   => 'page',
						'label' => __( 'Single Page', 'astra-portfolio' ),
					),
				)
			);

			return $all_types;
		}

		/**
		 * View actions
		 */
		public static function get_view_actions() {

			if ( empty( self::$view_actions ) ) {

				$actions            = array(
					'general'  => array(
						'label'    => __( 'General', 'astra-portfolio' ),
						'show'     => true,
						'priority' => 10,
					),
					'style'    => array(
						'label'    => __( 'Style', 'astra-portfolio' ),
						'show'     => true,
						'priority' => 20,
					),
					'advanced' => array(
						'label'    => __( 'Advanced', 'astra-portfolio' ),
						'show'     => true,
						'priority' => 30,
					),
				);
				self::$view_actions = apply_filters( 'astra_portfolio_menu_options', $actions );
			}

			return self::$view_actions;
		}

		/**
		 * Save All admin settings here
		 */
		public static function save_settings() {

			// Only admins can save settings.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Process only if tab slug is set.
			if ( ! isset( $_REQUEST['tab_slug'] ) ) {
				return;
			}

			// Make sure we have a valid nonce.
			if ( isset( $_REQUEST['astra-portfolio-import'] ) && wp_verify_nonce( $_REQUEST['astra-portfolio-import'], 'astra-portfolio-importing' ) ) {

				// Stored Settings.
				$stored_data = Astra_Portfolio_Helper::get_page_settings();

				if ( 'general' === $_REQUEST['tab_slug'] ) {

					$stored_data['other-categories'] = ( isset( $_REQUEST['other-categories'] ) ) ? sanitize_text_field( $_REQUEST['other-categories'] ) : false;

					$stored_data['categories'] = ( isset( $_REQUEST['categories'] ) ) ? sanitize_text_field( $_REQUEST['categories'] ) : false;

					$stored_data['show-search'] = ( isset( $_REQUEST['show-search'] ) ) ? sanitize_text_field( $_REQUEST['show-search'] ) : false;

					$stored_data['page-builder'] = ( isset( $_REQUEST['page-builder'] ) ) ? sanitize_text_field( $_REQUEST['page-builder'] ) : '';

					$stored_data['responsive-button'] = ( isset( $_REQUEST['responsive-button'] ) ) ? sanitize_text_field( $_REQUEST['responsive-button'] ) : false;
				}

				if ( 'style' === $_REQUEST['tab_slug'] ) {

					if ( isset( $_REQUEST['show-portfolio-on'] ) ) {
						$stored_data['show-portfolio-on'] = sanitize_text_field( $_REQUEST['show-portfolio-on'] );
					}
					if ( isset( $_REQUEST['grid-style'] ) ) {
						$stored_data['grid-style'] = sanitize_text_field( $_REQUEST['grid-style'] );
					}
					if ( isset( $_REQUEST['preview-bar-loc'] ) ) {
						$stored_data['preview-bar-loc'] = sanitize_text_field( $_REQUEST['preview-bar-loc'] );
					}
					if ( isset( $_REQUEST['no-more-sites-message'] ) ) {
						$stored_data['no-more-sites-message'] = stripcslashes( $_REQUEST['no-more-sites-message'] );
					}

					$stored_data['enable-masonry'] = ( isset( $_REQUEST['enable-masonry'] ) ) ? sanitize_text_field( $_REQUEST['enable-masonry'] ) : false;

					if ( isset( $_REQUEST['no-of-columns'] ) ) {
						$stored_data['no-of-columns'] = absint( $_REQUEST['no-of-columns'] );
					}
					if ( isset( $_REQUEST['par-page'] ) ) {
						$stored_data['par-page'] = absint( $_REQUEST['par-page'] );
					}
				}

				if ( 'advanced' === $_REQUEST['tab_slug'] ) {

					if ( isset( $_REQUEST['rewrite'] ) ) {
						$stored_data['rewrite'] = sanitize_title( $_REQUEST['rewrite'] );
					}
					if ( isset( $_REQUEST['rewrite-tags'] ) ) {
						$stored_data['rewrite-tags'] = sanitize_title( $_REQUEST['rewrite-tags'] );
					}
					if ( isset( $_REQUEST['rewrite-categories'] ) ) {
						$stored_data['rewrite-categories'] = sanitize_title( $_REQUEST['rewrite-categories'] );
					}
					if ( isset( $_REQUEST['rewrite-other-categories'] ) ) {
						$stored_data['rewrite-other-categories'] = sanitize_title( $_REQUEST['rewrite-other-categories'] );
					}
				}

				// Update settings.
				update_option( 'astra-portfolio-settings', $stored_data );

				// Rewrite permalinks if new rewrite string found.
				if (
					isset( $_REQUEST['rewrite'] ) ||
					isset( $_REQUEST['rewrite-tags'] ) ||
					isset( $_REQUEST['rewrite-categories'] ) ||
					isset( $_REQUEST['rewrite-other-categories'] )
				) {
					flush_rewrite_rules();
				}
			}

			// Let extensions hook into saving.
			do_action( 'astra_portfolio_settings_save' );
		}

		/**
		 * Enqueues the needed CSS/JS for Backend.
		 *
		 * @param  string $hook Current hook.
		 *
		 * @since 1.0.0
		 */
		public static function admin_scripts( $hook = '' ) {

			if ( 'astra-portfolio_page_astra-portfolio' === $hook ) {
				wp_register_script( 'astra-portfolio-api', ASTRA_PORTFOLIO_URI . 'assets/js/' . Astra_Portfolio::get_instance()->get_assets_js_path( 'astra-portfolio-api' ), array( 'jquery' ), ASTRA_PORTFOLIO_VER, true );
				wp_enqueue_style( 'astra-portfolio-admin-page', ASTRA_PORTFOLIO_URI . 'assets/css/' . Astra_Portfolio::get_instance()->get_assets_css_path( 'admin-page' ), null, ASTRA_PORTFOLIO_VER, 'all' );
				wp_enqueue_script( 'astra-portfolio-admin-page', ASTRA_PORTFOLIO_URI . 'assets/js/' . Astra_Portfolio::get_instance()->get_assets_js_path( 'admin-page' ), array( 'jquery' ), ASTRA_PORTFOLIO_VER, true );
				$l10n = array(
					'admin_page_url' => admin_url( 'edit.php?post_type=astra-portfolio' ),
				);
				wp_localize_script( 'astra-portfolio-admin-page', 'AstraPortfolioAdminPageVars', $l10n );
			}

			if ( 'astra-portfolio_page_astra-portfolio-add-new' === $hook ) {
				wp_enqueue_style( 'astra-portfolio-add-new-form', ASTRA_PORTFOLIO_URI . 'assets/css/' . Astra_Portfolio::get_instance()->get_assets_css_path( 'add-new-form' ), null, ASTRA_PORTFOLIO_VER, 'all' );
			}
		}

		/**
		 * Init Nav Menu
		 *
		 * @param mixed $action Action name.
		 * @since 1.0.0
		 */
		public static function init_nav_menu( $action = '' ) {

			if ( '' !== $action ) {
				self::render_tab_menu( $action );
			}
		}

		/**
		 * Render tab menu
		 *
		 * @param mixed $action Action name.
		 * @since 1.0.0
		 */
		public static function render_tab_menu( $action = '' ) {
			?>
			<div id="astra-portfolio-menu-page" class="wrap">
				<h1><?php esc_html_e( 'WP Portfolio', 'astra-portfolio' ); ?></h1>
				<?php self::render( $action ); ?>
			</div>
			<?php
		}

		/**
		 * Prints HTML content for tabs
		 *
		 * @param mixed $action Action name.
		 * @since 1.0.0
		 */
		public static function render( $action ) {
			?>
			<div class="nav-tab-wrapper">

				<?php
				$view_actions = self::get_view_actions();

				foreach ( $view_actions as $slug => $data ) {

					if ( ! $data['show'] ) {
						continue;
					}

					$url = self::get_page_url( $slug );

					if ( $slug === self::$parent_page_slug ) {
						update_option( 'astra_parent_page_url', $url );
					}

					$active = ( $slug === $action ) ? 'nav-tab-active' : '';
					?>
						<a class='nav-tab <?php echo esc_attr( $active ); ?>' href='<?php echo esc_url( $url ); ?>'> <?php echo esc_html( $data['label'] ); ?> </a>
				<?php } ?>
			</div><!-- .nav-tab-wrapper -->

			<?php
			// Settings update message.
			if ( isset( $_REQUEST['message'] ) && ( 'saved' === $_REQUEST['message'] || 'saved_ext' === $_REQUEST['message'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				?>
					<div id="message" class="notice notice-success is-dismissive"><p> <?php esc_html_e( 'Settings saved successfully.', 'astra-portfolio' ); ?> </p></div>
				<?php
			}

		}

		/**
		 * Get and return page URL
		 *
		 * @param string $menu_slug Menu name.
		 * @since 1.0.0
		 * @return  string page url
		 */
		public static function get_page_url( $menu_slug ) {

			$parent_page = self::$default_menu_position;

			if ( strpos( $parent_page, '?' ) !== false ) {
				$query_var = '&page=' . self::$plugin_slug;
			} else {
				$query_var = '?page=' . self::$plugin_slug;
			}

			$parent_page_url = admin_url( $parent_page . $query_var );

			$url = $parent_page_url . '&action=' . $menu_slug;

			return esc_url( $url );
		}

		/**
		 * Add main menu
		 *
		 * @since 1.0.0
		 */
		public static function add_admin_menu() {

			$parent_page    = self::$default_menu_position;
			$page_title     = self::$menu_page_title;
			$capability     = 'manage_options';
			$page_menu_slug = self::$plugin_slug;
			$page_menu_func = __CLASS__ . '::menu_callback';

			add_submenu_page( 'edit.php?post_type=astra-portfolio', $page_title, $page_title, $capability, $page_menu_slug, $page_menu_func );
		}

		/**
		 * Menu callback
		 *
		 * @since 1.0.0
		 */
		public static function menu_callback() {

			$current_slug = isset( $_GET['action'] ) ? esc_attr( $_GET['action'] ) : self::$current_slug; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$active_tab   = str_replace( '_', '-', $current_slug );
			$current_slug = str_replace( '-', '_', $current_slug );

			?>
			<div class="astra-portfolio-menu-page-wrapper">
				<?php self::init_nav_menu( $active_tab ); ?>
				<?php do_action( 'astra_portfolio_menu_' . esc_attr( $current_slug ) . '_action' ); ?>
			</div>
			<?php
		}

		/**
		 * Check Cron Status
		 *
		 * Gets the current cron status by performing a test spawn. Cached for one hour when all is well.
		 *
		 * @since 1.7.0
		 *
		 * @param bool $cache Whether to use the cached result from previous calls.
		 * @return true|WP_Error Boolean true if the cron spawner is working as expected, or a WP_Error object if not.
		 */
		public static function test_cron( $cache = true ) {
			global $wp_version;

			if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
				return new WP_Error( 'wp_portfolio_cron_error', __( 'ERROR! Cron schedules are disabled by setting constant DISABLE_WP_CRON to true.<br/>To start the import process please enable the cron by setting false. E.g. define( \'DISABLE_WP_CRON\', false );', 'astra-portfolio' ) );
			}

			if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
				return new WP_Error( 'wp_portfolio_cron_error', __( 'ERROR! Cron schedules are disabled by setting constant ALTERNATE_WP_CRON to true.<br/>To start the import process please enable the cron by setting false. E.g. define( \'ALTERNATE_WP_CRON\', false );', 'astra-portfolio' ) );
			}

			$cached_status = get_transient( 'astra-portfolio-cron-test-ok' );

			if ( $cache && $cached_status ) {
				return true;
			}

			$sslverify     = version_compare( $wp_version, 4.0, '<' );
			$doing_wp_cron = sprintf( '%.22F', microtime( true ) );

			$cron_request = apply_filters(
				'cron_request',
				array(
					'url'  => site_url( 'wp-cron.php?doing_wp_cron=' . $doing_wp_cron ),
					'key'  => $doing_wp_cron,
					'args' => array(
						'timeout'   => 3,
						'blocking'  => true,
						'sslverify' => apply_filters( 'https_local_ssl_verify', $sslverify ),
					),
				)
			);

			$cron_request['args']['blocking'] = true;

			$result = wp_remote_post( $cron_request['url'], $cron_request['args'] );

			if ( is_wp_error( $result ) ) {
				return $result;
			} elseif ( wp_remote_retrieve_response_code( $result ) >= 300 ) {
				return new WP_Error(
					'unexpected_http_response_code',
					sprintf(
						/* translators: 1: The HTTP response code. */
						__( 'Unexpected HTTP response code: %s', 'astra-portfolio' ),
						intval( wp_remote_retrieve_response_code( $result ) )
					)
				);
			} else {
				set_transient( 'astra-portfolio-cron-test-ok', 1, 3600 );
				return true;
			}

		}

		/**
		 * Include General page
		 *
		 * @since 1.0.0
		 * @since 1.7.0 Convert into the General page tab.
		 */
		public static function general_page() {

			$data = Astra_Portfolio_Helper::get_page_settings();

			$status = get_option( 'astra-portfolio-batch-process' );

			require_once ASTRA_PORTFOLIO_DIR . 'includes/general-page.php';
		}

		/**
		 * Include Style Page
		 *
		 * @since 1.7.0
		 */
		public static function style_page() {

			$data = Astra_Portfolio_Helper::get_page_settings();

			$status = get_option( 'astra-portfolio-batch-process' );

			require_once ASTRA_PORTFOLIO_DIR . 'includes/style-page.php';
		}

		/**
		 * Include Advanced page
		 *
		 * @since 1.7.0
		 */
		public static function advanced_page() {

			$data = Astra_Portfolio_Helper::get_page_settings();

			$status = get_option( 'astra-portfolio-batch-process' );

			require_once ASTRA_PORTFOLIO_DIR . 'includes/advanced-page.php';
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @param   mixed $links Plugin Action links.
		 * @return  array
		 */
		public function action_links( $links ) {
			$action_links = array(
				'settings' => '<a href="' . admin_url( 'edit.php?post_type=astra-portfolio&page=astra-portfolio' ) . '" aria-label="' . esc_attr__( 'Settings', 'astra-portfolio' ) . '">' . esc_html__( 'Settings', 'astra-portfolio' ) . '</a>',
			);

			return array_merge( $action_links, $links );
		}

		/**
		 * Default portfolio type
		 *
		 * @since 1.3.0
		 *
		 * @return mixed
		 */
		public static function get_default_portfolio_type() {

			$default_type = apply_filters( 'astra_portfolio_default_portfolio_type', '' );

			$types = self::get_portfolio_types();

			foreach ( $types as $key => $type ) {
				if ( $type['key'] === $default_type ) {
					return $default_type;
				}
			}

			return '';
		}

		/**
		 * Complete Import Sites
		 *
		 * @since 1.8.0
		 * @return boolean
		 */
		public function complete_import_sites() {
			$site_import_count = (int) get_option( 'astra-portfolio-site-import-count', 0 );
			$exclude_ids       = (array) get_option( 'astra_portfolio_batch_excluded_sites', array() );
			$total_requests    = (array) get_option( 'astra_portfolio_total_requests', array( 'total' => '' ) );
			$total             = (int) $total_requests['total'];

			if ( $exclude_ids ) {
				$site_import_count = count( $exclude_ids );
			}

			if ( 0 === $total ) {
				return true;
			}

			if ( $site_import_count >= $total ) {
				return true;
			}

			return false;
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Astra_Portfolio_Page::get_instance();

endif;
