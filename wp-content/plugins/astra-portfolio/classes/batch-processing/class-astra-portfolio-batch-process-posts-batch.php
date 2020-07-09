<?php
/**
 * Background Process for Posts
 *
 * @package Astra Portfolio
 * @since 1.4.2
 */

if ( class_exists( 'WP_Background_Process' ) ) :

	/**
	 * Image Background Process
	 *
	 * @since 1.4.2
	 */
	class Astra_Portfolio_Batch_Process_Posts_Batch extends WP_Background_Process {

		/**
		 * Image Process
		 *
		 * @var string
		 */
		protected $action = 'import_astra_sites';

		/**
		 * Task
		 *
		 * Override this method to perform any actions required on each
		 * queue item. Return the modified item for further processing
		 * in the next pass through. Or, return false to remove the
		 * item from the queue.
		 *
		 * @since 1.4.2
		 *
		 * @param object $item Queue item object.
		 * @return mixed
		 */
		protected function task( $item ) {
			Astra_Portfolio_Batch_Process_Posts::get_instance()->import_all();
			return false;
		}
	}

endif;
