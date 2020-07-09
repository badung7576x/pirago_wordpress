<?php
/**
 * Batch Processing
 *
 * @package Astra Portfolio
 * @since 1.0.0
 */

if ( ! class_exists( 'Astra_Portfolio_Batch_Process_Terms' ) ) :

	/**
	 * Astra_Portfolio_Batch_Process_Terms
	 *
	 * @since 1.0.0
	 */
	class Astra_Portfolio_Batch_Process_Terms {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object Class object.
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
		}

		/**
		 * Import
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function import() {
			$taxonomies = array(
				'astra-portfolio-categories'       => 'astra-site-category',
				'astra-portfolio-other-categories' => 'astra-site-page-builder',
			);
			foreach ( $taxonomies as $new_taxonomy => $taxonomy ) {
				$terms = Astra_Portfolio_API::get_instance()->get_categories( $taxonomy );

				if ( $terms ) {
					$term_mapping = array();
					foreach ( $terms as $key => $term ) {
						$term_mapping[] = array(
							'name' => ( isset( $term['name'] ) ) ? $term['name'] : '',
							'args' => array(
								'alias_of'    => ( isset( $term['alias_of'] ) ) ? $term['alias_of'] : '',
								'description' => ( isset( $term['description'] ) ) ? $term['description'] : '',
								'parent'      => ( isset( $term['parent'] ) ) ? $term['parent'] : '',
								'slug'        => ( isset( $term['slug'] ) ) ? $term['slug'] : '',
							),
						);
					}

					update_option( 'astra-portfolio-batch-process-string', 'Importing Categories..' );

					Astra_Portfolio_Admin::get_instance()->add_terms( $new_taxonomy, $term_mapping );
					update_option( 'astra-portfolio-batch-api-' . $taxonomy, $terms );
				}
			}
		}

		/**
		 * Get New Taxonomy IDs.
		 *
		 * @since 1.0.0
		 *
		 * @param  string $current_taxonomy Current site taxonomy.
		 * @param  string $taxonomy         Rest API taxonomy.
		 * @param  array  $rest_api_tax_ids Rest API taxonomy IDs.
		 * @return array   Taxonomy mapping IDs.
		 */
		public static function get_new_taxonomy_ids( $current_taxonomy = '', $taxonomy = '', $rest_api_tax_ids = array() ) {
			if ( empty( $rest_api_tax_ids ) || empty( $taxonomy ) ) {
				return $rest_api_tax_ids;
			}

			$new_api_terms = self::get_taxonomy_mapping( $current_taxonomy, $taxonomy );
			if ( empty( $new_api_terms ) ) {
				return $rest_api_tax_ids;
			}

			$mapping_term_ids = array();
			foreach ( $new_api_terms as $new_api_term_key => $new_api_term ) {

				foreach ( $rest_api_tax_ids as $request_term_key => $request_term ) {
					if ( isset( $new_api_term['id'] ) && isset( $new_api_term['stored_id'] ) ) {
						if ( $new_api_term['id'] === $request_term ) {
							$mapping_term_ids[] = $new_api_term['stored_id'];
						}
					}
				}
			}

			return $mapping_term_ids;
		}

		/**
		 * Get Taxonomy Mapping
		 *
		 * @since 1.0.0
		 *
		 * @param  string $new_taxonomy New taxonomy.
		 * @param  string $taxonomy     Taxonomy.
		 * @return array                Stored taxonomy mapping.
		 */
		public static function get_taxonomy_mapping( $new_taxonomy = '', $taxonomy = '' ) {

			$data = get_option( 'astra-portfolio-batch-' . $taxonomy, false );
			if ( $data ) {
				return $data;
			}

			$response_terms = get_option( 'astra-portfolio-batch-api-' . $taxonomy );
			if ( empty( $response_terms ) ) {
				$response_terms = Astra_Portfolio_API::get_instance()->get_categories( $taxonomy );
			}

			$new_api_terms = array();

			if ( ! empty( $response_terms ) ) {
				foreach ( $response_terms as $key => $response_term ) {
					$response_term = (array) $response_term;

					if ( ! empty( $response_term ) ) {
						$data = array(
							'id'   => ( isset( $response_term['id'] ) ) ? $response_term['id'] : '',
							'slug' => ( isset( $response_term['slug'] ) ) ? $response_term['slug'] : '',
						);

						if ( isset( $response_term['slug'] ) ) {
							$term_exist = term_exists( $response_term['slug'], $new_taxonomy );

							if ( $term_exist ) {
								$data['stored_id'] = $term_exist['term_id'];
							}
						}

						$new_api_terms[] = $data;
					}
				}
			}

			update_option( 'astra-portfolio-batch-' . $taxonomy, $new_api_terms );

			return $new_api_terms;
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Astra_Portfolio_Batch_Process_Terms::get_instance();

endif;
