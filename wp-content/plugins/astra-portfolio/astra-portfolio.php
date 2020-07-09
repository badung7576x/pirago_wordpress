<?php
/**
 * Plugin Name: WP Portfolio
 * Plugin URI: http://www.wpastra.com/pro/
 * Description: Display the portfolio of Starter Sites & other portfolio items easily on your website.
 * Version: 1.9.0
 * Author: Brainstorm Force
 * Author URI: http://www.brainstormforce.com
 * Text Domain: astra-portfolio
 *
 * @package Astra Portfolio
 */

/**
 * Set constants.
 */
define( 'ASTRA_PORTFOLIO_VER', '1.9.0' );
define( 'ASTRA_PORTFOLIO_FILE', __FILE__ );
define( 'ASTRA_PORTFOLIO_BASE', plugin_basename( ASTRA_PORTFOLIO_FILE ) );
define( 'ASTRA_PORTFOLIO_DIR', plugin_dir_path( ASTRA_PORTFOLIO_FILE ) );
define( 'ASTRA_PORTFOLIO_URI', plugins_url( '/', ASTRA_PORTFOLIO_FILE ) );

require_once ASTRA_PORTFOLIO_DIR . 'classes/class-astra-portfolio.php';

// Brainstorm Updater.
require_once ASTRA_PORTFOLIO_DIR . 'class-brainstorm-updater-astra-portfolio.php';
