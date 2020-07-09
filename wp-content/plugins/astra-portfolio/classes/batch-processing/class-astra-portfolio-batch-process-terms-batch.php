<?php
/**
 * Image Background Process
 *
 * @package Astra Portfolio
 * @since 1.0.0
 */

if ( class_exists( 'WP_Background_Process' ) ) :

	/**
	 * Image Background Process
	 *
	 * @since 1.0.0
	 */
	class Astra_Portfolio_Batch_Process_Terms_Batch extends WP_Background_Process {

		/**
		 * Image Process
		 *
		 * @var string
		 */
		protected $action = 'import_astra_site_terms';

		/**
		 * Task
		 *
		 * Override this method to perform any actions required on each
		 * queue item. Return the modified item for further processing
		 * in the next pass through. Or, return false to remove the
		 * item from the queue.
		 *
		 * @since 1.0.0
		 *
		 * @param object $process Queue item object.
		 * @return mixed
		 */
		protected function task( $process ) {

			if ( method_exists( $process, 'import' ) ) {
				$process->import();
			}

			return false;
		}

		/**
		 * Complete
		 *
		 * Override if applicable, but ensure that the below actions are
		 * performed, or, call parent::complete().
		 *
		 * @since 1.0.0
		 */
		protected function complete() {

			astra_portfolio_error_log( 'Imported all categories sites.' );
			parent::complete();

			update_option( 'astra-portfolio-batch-process-string', 'Categories Imported!' );
		}

	}

endif;
