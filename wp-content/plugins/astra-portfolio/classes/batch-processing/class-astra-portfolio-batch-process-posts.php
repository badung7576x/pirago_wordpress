<?php
/**
 * Batch Process for Posts
 *
 * @package Astra Portfolio
 * @since 1.0.0
 */

if ( ! class_exists( 'Astra_Portfolio_Batch_Process_Posts' ) ) :

	/**
	 * Astra_Portfolio_Batch_Process_Posts
	 *
	 * @since 1.0.0
	 */
	class Astra_Portfolio_Batch_Process_Posts {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Portfolio downloaded
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object Class object.
		 */
		private static $current_portfolio_downloaded = false;

		/**
		 * Process Posts
		 *
		 * @since 1.0.0
		 * @var object Class object.
		 * @access public
		 */
		public $process_posts_single;

		/**
		 * Process Posts
		 *
		 * @since 1.0.0
		 * @var object Class object.
		 * @access public
		 */
		public $process_posts_images;

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
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			require_once ASTRA_PORTFOLIO_DIR . 'classes/batch-processing/class-astra-portfolio-batch-process-posts-single-batch.php';
			$this->process_posts_single = new Astra_Portfolio_Batch_Process_Posts_Single_Batch();

			require_once ASTRA_PORTFOLIO_DIR . 'classes/batch-processing/class-astra-portfolio-batch-process-posts-images-batch.php';
			$this->process_posts_images = new Astra_Portfolio_Batch_Process_Posts_Images_Batch();

			add_action( 'admin_head', array( $this, 'is_process_complete' ) );
		}

		/**
		 * Check Is Batch Process Complete
		 *
		 * @return void
		 */
		public function is_process_complete() {
			$process_complete = get_option( 'astra-portfolio-batch-process-all-complete', 'no' );

			if ( 'yes' === $process_complete ) {
				astra_portfolio_error_log( '========= {COMPLETE} Imported All Portfolio Items =========' );

				// Mapping.
				delete_option( 'astra-portfolio-batch-api-astra-portfolio-categories' );
				delete_option( 'astra-portfolio-batch-api-astra-portfolio-other-categories' );

				// Taxonomy magging option.
				delete_option( 'astra-portfolio-batch-astra-site-category' );
				delete_option( 'astra-portfolio-batch-astra-site-page-builder' );
				delete_option( 'astra-portfolio-batch-astra-portfolio-categories' );
				delete_option( 'astra-portfolio-batch-astra-portfolio-other-categories' );

				// Update the current status.
				update_option( 'astra-portfolio-batch-process', 'complete' );

				// Update all status.
				delete_option( 'astra-portfolio-batch-process-all-complete' );

				delete_option( 'astra-portfolio-image-import-count' );
				delete_option( 'astra-portfolio-site-import-count' );
				$total_requests = (array) get_option(
					'astra_portfolio_total_requests',
					array(
						'total'      => 0,
						'totalpages' => 0,
					)
				);
				if ( isset( $total_requests['totalpages'] ) && $total_requests['totalpages'] ) {
					for ( $page = 1; $page <= $total_requests['totalpages']; $page++ ) {
						delete_option( 'astra_portfolio_site_page_' . $page );
					}
				}
				delete_option( 'astra_portfolio_total_requests' );
			}
		}

		/**
		 * Import
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function import_all() {

			astra_portfolio_error_log( ' =========== Adding Portfolios to [Image & Portfolio] Queue ===========' );

			update_option( 'astra-portfolio-batch-process-string', 'Importing Sites..' );

			// Posts.
			$total_requests = $this->get_total_requests();
			$total          = $total_requests['total'];
			$totalpages     = $total_requests['totalpages'];
			if ( $totalpages ) {
				for ( $page = 1; $page <= $totalpages; $page++ ) {

					$response = $this->get_sites( $page );

					if ( $response['sites_count'] ) {

						$site = ( 1 === $page ) ? 1 : 101;

						foreach ( $response['sites'] as $key => $single_site ) {

							$remote_post_id = ( isset( $single_site['id'] ) ) ? absint( $single_site['id'] ) : '';

							// Debugging.
							$title                = ( isset( $single_site['title'] ) ) ? $single_site['title'] : '';
							$single_site['total'] = $total;

							$this->process_posts_images->push_to_queue( $single_site );
							$this->process_posts_single->push_to_queue( $single_site );

							$site++;
						}
					}
				}
			}

			// Import Images dispatch queue.
			$this->process_posts_images->save()->dispatch();

			// Posts dispatch queue.
			$this->process_posts_single->save()->dispatch();
		}

		/**
		 * Get Sites
		 *
		 * @since 1.7.0
		 *
		 * @param  int $page Page number.
		 * @return array Sites JSON.
		 */
		public function get_sites( $page ) {

			$site_page = get_option( 'astra_portfolio_site_page_' . $page, array() );

			if ( ! empty( $site_page ) ) {
				return $site_page;
			}

			$response = Astra_Portfolio_API::get_instance()->get_sites( array( 'page' => $page ) );

			if ( $response['sites_count'] ) {
				update_option( 'astra_portfolio_site_page_' . $page, $response );
			}

			return $response;
		}

		/**
		 * Get Total Requests
		 *
		 * @return integer
		 */
		public function get_total_requests() {

			$total_requests = get_option( 'astra_portfolio_total_requests', array() );

			if ( ! empty( $total_requests ) ) {
				return $total_requests;
			}

			$response = Astra_Portfolio_API::get_instance()->get_sites();

			if ( isset( $response['x-wp-totalpages'] ) ) {
				$total_requests = array(
					'total'      => $response['x-wp-total'],
					'totalpages' => $response['x-wp-totalpages'],
				);

				update_option( 'astra_portfolio_total_requests', $total_requests );

				return $total_requests;
			}

			$this->get_total_requests();
		}

		/**
		 * Import Single Image
		 *
		 * @param  array $single_site Single site API response.
		 * @return void
		 */
		public function import_single_image( $single_site = array() ) {

			$image_import_count = get_option( 'astra-portfolio-image-import-count', 0 );
			$exclude_ids        = (array) get_option( 'astra_portfolio_batch_excluded_sites', array() );
			if ( $exclude_ids ) {
				$image_import_count = count( $exclude_ids );
			}

			if ( $image_import_count < $single_site['total'] ) {
				++$image_import_count;
			}

			update_option( 'astra-portfolio-batch-process-string', 'Importing Images ' . $image_import_count . ' of ' . $single_site['total'] );

			$remote_post_id = ( isset( $single_site['id'] ) ) ? absint( $single_site['id'] ) : '';
			$title          = ( isset( $single_site['title'] ) ) ? $single_site['title'] : '';

			$featured_image_url = ( isset( $single_site['featured_image_url'] ) ) ? $single_site['featured_image_url'] : '';

			if ( ! empty( $featured_image_url ) ) {

				$image = array(
					'id'  => wp_rand( 0000, 9999 ),
					'url' => $featured_image_url,
				);

				$downloaded_image = Astra_Portfolio_Import_Image::get_instance()->import( $image );
			}

			update_option( 'astra-portfolio-image-import-count', $image_import_count );

			// Log the current item.
			$this->check_remaining_items();
		}

		/**
		 * Import Single Post
		 *
		 * @param  array $single_site Single site API response.
		 * @return void
		 */
		public function import_single_post( $single_site = array() ) {

			$site_import_count = (int) get_option( 'astra-portfolio-site-import-count', 0 );
			$exclude_ids       = (array) get_option( 'astra_portfolio_batch_excluded_sites', array() );
			if ( $exclude_ids ) {
				$site_import_count = count( $exclude_ids );
			}

			if ( $site_import_count < $single_site['total'] ) {
				++$site_import_count;
			}

			update_option( 'astra-portfolio-batch-process-string', 'Importing Sites ' . $site_import_count . ' of ' . $single_site['total'] );

			// Process item.
			$remote_post_id = ( isset( $single_site['id'] ) ) ? absint( $single_site['id'] ) : '';
			$title          = ( isset( $single_site['title'] ) ) ? $single_site['title'] : '';

			if ( ! in_array( $remote_post_id, $exclude_ids, true ) ) {

				// Add to excludes list.
				$exclude_ids[] = $remote_post_id;
				$exclude_ids   = array_unique( $exclude_ids ); // Remove duplicates.

				if ( update_option( 'astra_portfolio_batch_excluded_sites', $exclude_ids ) ) {

					$title              = ( isset( $single_site['title'] ) ) ? $single_site['title'] : '';
					$content            = ( isset( $single_site['content'] ) ) ? $single_site['content'] : '';
					$site_url           = ( isset( $single_site['astra_demo_url'] ) ) ? $single_site['astra_demo_url'] : '';
					$featured_image_url = ( isset( $single_site['featured_image_url'] ) ) ? $single_site['featured_image_url'] : '';

					$api_terms_site_category     = ( isset( $single_site['astra-site-category'] ) ) ? $single_site['astra-site-category'] : array();
					$api_terms_site_page_builder = ( isset( $single_site['astra-site-page-builder'] ) ) ? $single_site['astra-site-page-builder'] : array();

					$site_category     = Astra_Portfolio_Batch_Process_Terms::get_instance()->get_new_taxonomy_ids( 'astra-portfolio-categories', 'astra-site-category', $api_terms_site_category );
					$site_page_builder = Astra_Portfolio_Batch_Process_Terms::get_instance()->get_new_taxonomy_ids( 'astra-portfolio-other-categories', 'astra-site-page-builder', $api_terms_site_page_builder );

					// New post argument array.
					$args = array(
						'post_type'   => 'astra-portfolio',
						'post_status' => 'draft',
						'post_title'  => $title,
						'meta_input'  => array(
							'astra-remote-post-id' => $remote_post_id,
							'astra-site-url'       => $site_url,
							'astra-portfolio-type' => 'iframe',
						),
						'tax_input'   => array(
							'astra-portfolio-categories' => $site_category,
							'astra-portfolio-other-categories' => $site_page_builder,
						),
					);

					// Create new post and get new post ID.
					$post_id = wp_insert_post( $args );

					if ( $post_id && ! is_wp_error( $post_id ) ) {

						update_option( 'astra-portfolio-site-import-count', ( $site_import_count ) );
						astra_portfolio_error_log( 'Success! Imported Portfolio - ' . $title );

						// Add post terms.
						wp_set_post_terms( $post_id, $site_category, 'astra-portfolio-categories' );
						wp_set_post_terms( $post_id, $site_page_builder, 'astra-portfolio-other-categories' );

						if ( ! empty( $featured_image_url ) ) {

							$image = array(
								'id'  => wp_rand( 0000, 9999 ),
								'url' => $featured_image_url,
							);

							$downloaded_image = Astra_Portfolio_Import_Image::get_instance()->import( $image );

							// Is image downloaded.
							if ( $downloaded_image['id'] !== $image['id'] ) {
								// And finally assign featured image to post.
								set_post_thumbnail( $post_id, $downloaded_image['id'] );

								// Add portfolio image meta.
								update_post_meta( $post_id, 'astra-portfolio-image-id', $downloaded_image['id'] );
							}
						}
					} else {
						$message = 'Portfolio already exist!';
						if ( is_wp_error( $post_id ) ) {
							$message = $post_id->get_error_message();
						}
						astra_portfolio_error_log( 'Failed! ' . $message . ' - ' . $title );
					}
				}
			}

			// Log the current item.
			$this->check_remaining_items();
		}

		/**
		 * Check renaming items
		 *
		 * @since 1.7.0
		 *
		 * @return null if there is not total items.
		 */
		public function check_remaining_items() {
			$site_import_count = (int) get_option( 'astra-portfolio-site-import-count', 0 );
			$exclude_ids       = (array) get_option( 'astra_portfolio_batch_excluded_sites', array() );
			$total_requests    = (array) get_option( 'astra_portfolio_total_requests', array( 'total' => '' ) );
			$total             = (int) $total_requests['total'];

			if ( $exclude_ids ) {
				$site_import_count = count( $exclude_ids );
			}

			if ( 0 === $total ) {
				return;
			}

			astra_portfolio_error_log( 'Imported ' . $site_import_count . ' out of ' . $total );

			if ( $site_import_count >= $total ) {
				update_option( 'astra-portfolio-batch-process-all-complete', 'yes' );
				delete_option( 'astra-portfolio-batch-process-string' );
			}
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Astra_Portfolio_Batch_Process_Posts::get_instance();

endif;
