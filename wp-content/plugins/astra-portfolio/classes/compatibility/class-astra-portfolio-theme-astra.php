<?php
/**
 * Astra Theme Compatibility
 *
 * @package Astra Portfolio
 * @since 1.0.0
 */

if ( ! class_exists( 'Astra_Portfolio_Theme_Astra' ) ) :

	/**
	 * Astra_Portfolio_Theme_Astra
	 *
	 * @since 1.0.0
	 */
	class Astra_Portfolio_Theme_Astra {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class object.
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
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			add_action( 'after_setup_theme', array( $this, 'theme_setup' ) );

		}

		/**
		 * Theme Setup
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function theme_setup() {

			$theme = wp_get_theme();
			if ( 'astra' === $theme->get( 'TextDomain' ) ) {
				add_filter( 'astra_portfolio_row_class', array( $this, 'row_class' ) );
				add_filter( 'astra_portfolio_column_classes', array( $this, 'column_classes' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
			}

		}

		/**
		 * Row Class
		 *
		 * @since 1.0.0
		 *
		 * @return string Row class.
		 */
		public function row_class() {
			return 'ast-row';
		}

		/**
		 * Column Class
		 *
		 * @since 1.0.0
		 *
		 * @return string Column class.
		 */
		public function column_classes() {

			return array(
				'1' => 'ast-col-md-12',
				'2' => 'ast-col-md-6',
				'3' => 'ast-col-md-4',
				'4' => 'ast-col-md-3',
			);

		}

		/**
		 * De-register
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function scripts() {
			wp_deregister_style( 'astra-portfolio-grid' );
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Astra_Portfolio_Theme_Astra::get_instance();

endif;
