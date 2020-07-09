<?php
/**
 * Functions
 *
 * @package Astra Portfolio
 * @since 1.9.0
 */

if ( ! function_exists( 'astra_portfolio_error_log' ) ) :
	/**
	 * Log Messages
	 *
	 * @since 1.9.0
	 * @param  string $message Log message.
	 * @return void
	 */
	function astra_portfolio_error_log( $message = '' ) {
		error_log( $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
endif;
