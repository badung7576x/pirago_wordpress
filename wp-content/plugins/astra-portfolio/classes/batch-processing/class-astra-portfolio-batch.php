<?php
/**
 * Batch Processing
 *
 * @package Astra Portfolio
 * @since 1.0.0
 */

if ( ! class_exists( 'Astra_Portfolio_Batch' ) ) :

	/**
	 * Astra_Portfolio_Batch
	 *
	 * @since 1.0.0
	 */
	class Astra_Portfolio_Batch {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 * @var object Class object.
		 * @access private
		 */
		private static $instance;

		/**
		 * Process All
		 *
		 * @since 1.0.0
		 * @var object Class object.
		 * @access public
		 */
		public $process_terms;

		/**
		 * Process Posts
		 *
		 * @since 1.4.2
		 * @var object Class object.
		 * @access public
		 */
		public $process_posts;

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

			// Core Helpers - Image Download.
			require_once ASTRA_PORTFOLIO_DIR . 'classes/batch-processing/helpers/class-astra-portfolio-import-image.php';

			// Core Helpers - Batch Processing.
			require_once ASTRA_PORTFOLIO_DIR . 'classes/batch-processing/helpers/class-wp-async-request.php';
			require_once ASTRA_PORTFOLIO_DIR . 'classes/batch-processing/helpers/class-wp-background-process.php';

			// Process.
			require_once ASTRA_PORTFOLIO_DIR . 'classes/batch-processing/class-astra-portfolio-batch-process-terms-batch.php';
			require_once ASTRA_PORTFOLIO_DIR . 'classes/batch-processing/class-astra-portfolio-batch-process-terms.php';
			require_once ASTRA_PORTFOLIO_DIR . 'classes/batch-processing/class-astra-portfolio-batch-process-posts-batch.php';
			require_once ASTRA_PORTFOLIO_DIR . 'classes/batch-processing/class-astra-portfolio-batch-process-posts.php';

			$this->process_terms = new Astra_Portfolio_Batch_Process_Terms_Batch();
			$this->process_posts = new Astra_Portfolio_Batch_Process_Posts_Batch();
			add_action( 'admin_head', array( $this, 'start_process' ) );
		}

		/**
		 * Start Image Import
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function start_process() {

			$status = get_option( 'astra-portfolio-batch-process', false );

			if ( 'complete' === $status ) {
				Astra_Portfolio_Notices::add_notice(
					array(
						'id'               => 'astra-portfolio-batch-process-complete',
						'type'             => 'success',
						'dismissible'      => false,
						'dismissible-meta' => 'transient',
						'show_if'          => true,
						/* translators: %1$s portfolio page url. */
						'message'          => sprintf( __( 'Starter Sites have been imported as portfolio items! Please <a href="%1$s">take a look</a> and publish the ones that you like.', 'astra-portfolio' ), esc_url( admin_url() . 'edit.php?post_type=astra-portfolio' ) ),
					)
				);

				delete_option( 'astra-portfolio-batch-process' );
				delete_option( 'astra-portfolio-batch-process-string' );

			} else {

				if ( ! isset( $_GET['_nonce'] ) || empty( $_GET['_nonce'] ) ) {
					return;
				}

				if ( wp_verify_nonce( $_GET['_nonce'], 'astra-portfolio-batch-process' ) ) {

					Astra_Portfolio_Notices::add_notice(
						array(
							'id'               => 'astra-portfolio-batch-process-start',
							'type'             => 'info',
							'dismissible'      => false,
							'dismissible-meta' => 'transient',
							'dismissible-time' => WEEK_IN_SECONDS,
							'show_if'          => true,
							'message'          => __( '<strong>Import Started!</strong><br/><br/>All Starter Sites will be imported as Portfolio items in a while. It might take a few minutes for the import process to complete, but you may resume your work as this happens in the background.<br/><br/>We will display another notice as soon as all Starter Sites are imported as portfolio items.', 'astra-portfolio' ),
						)
					);

					update_option( 'astra-portfolio-batch-process-string', 'Importing..' );

					update_option( 'astra-portfolio-batch-process', 'in-process' );

					// Posts.
					$this->process_posts->push_to_queue( Astra_Portfolio_Batch_Process_Posts::get_instance() );

					// Posts dispatch queue.
					$this->process_posts->save()->dispatch();

					// Terms.
					$this->process_terms->push_to_queue( Astra_Portfolio_Batch_Process_Terms::get_instance() );

					// Terms dispatch queue.
					$this->process_terms->save()->dispatch();
				}
			}
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Astra_Portfolio_Batch::get_instance();

endif;
