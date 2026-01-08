<?php
/**
 * Plugin Name: Storelyt Widget for WooCommerce
 * Plugin URI: https://lucian-web.dev/store-insights-for-woocommerce
 * Description: Store insights for WooCommerce displayed directly in your dashboard.
 * Version: 1.0.0
 * Author: Lucian-DEV
 * Author URI: https://lucian-web.dev
 * Text Domain: storelyt-widget-for-woocommerce
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * License: GPL v2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Storelyt Widget for WooCommerce is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 */

defined( 'ABSPATH' ) || exit;

// Define plugin constants.
define( 'STORELYTWIDGETWOO_VERSION', '1.0.0' );
define( 'STORELYTWIDGETWOO_PATH', plugin_dir_path( __FILE__ ) );
define( 'STORELYTWIDGETWOO_URL', plugin_dir_url( __FILE__ ) );

// Check for Composer autoload.
if ( ! file_exists( STORELYTWIDGETWOO_PATH . 'vendor/autoload.php' ) ) {
  return;
}

require_once STORELYTWIDGETWOO_PATH . 'vendor/autoload.php';

// Environment Checks
add_action( 'plugins_loaded', static function () {
  $min_wp  = '6.0';
  $min_php = '8.0';

  if ( version_compare( get_bloginfo( 'version' ), $min_wp, '<' ) || version_compare( PHP_VERSION, $min_php, '<' ) ) {
    add_action( 'admin_notices', static function () use ( $min_wp, $min_php ) {
      printf(
        '<div class="notice notice-error"><p>%s</p></div>',
        esc_html( sprintf( __( 'Storelyt Widget for WooCommerce requires WordPress %1$s+ and PHP %2$s+.', 'storelyt-widget-for-woocommerce' ), $min_wp, $min_php ) )
      );
    } );

    return;
  }

  if ( ! class_exists( 'WooCommerce' ) ) {
    add_action( 'admin_notices', static function () {
      echo '<div class="notice notice-error"><p>'
      . esc_html__( 'Storelyt Widget for WooCommerce requires WooCommerce to be installed and active.', 'storelyt-widget-for-woocommerce' )
        . '</p></div>';
    }
    );

    return;
  }

  if ( is_admin() ) {
    \StorelytWidgetWoo\Plugin::init();
  }
} );
