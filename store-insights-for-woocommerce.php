<?php
/**
 * Plugin Name: Store Insights for WooCommerce
 * Plugin URI: https://lucian-web.dev/store-insights-for-woocommerce
 * Description: Store insights for WooCommerce displayed directly in your dashboard.
 * Version: 1.0.0
 * Author: Lucian-DEV
 * Author URI: https://lucian-web.dev
 * Text Domain: store-insights-for-woocommerce
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * License: GPL v2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Store Insights for WooCommerce is free software: you can redistribute it and/or modify
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
define( 'STOREINSIGHTSWOO_VERSION', '1.0.0' );
define( 'STOREINSIGHTSWOO_PATH', plugin_dir_path( __FILE__ ) );
define( 'STOREINSIGHTSWOO_URL', plugin_dir_url( __FILE__ ) );

// Check for Composer autoload.
if ( ! file_exists( STOREINSIGHTSWOO_PATH . 'vendor/autoload.php' ) ) {
  return;
}

require_once STOREINSIGHTSWOO_PATH . 'vendor/autoload.php';

// Environment Checks
add_action( 'plugins_loaded', static function () {
  $min_wp  = '6.0';
  $min_php = '8.0';

  if ( version_compare( get_bloginfo( 'version' ), $min_wp, '<' ) || version_compare( PHP_VERSION, $min_php, '<' ) ) {
    add_action( 'admin_notices', static function () use ( $min_wp, $min_php ) {
      printf(
        '<div class="notice notice-error"><p>%s</p></div>',
        esc_html( sprintf( __( 'Store Insights for WooCommerce requires WordPress %1$s+ and PHP %2$s+.', 'store-insights-for-woocommerce' ), $min_wp, $min_php ) )
      );
    } );

    return;
  }

  if ( ! class_exists( 'WooCommerce' ) ) {
    add_action( 'admin_notices', static function () {
      echo '<div class="notice notice-error"><p>'
      . esc_html__( 'Store Insights for WooCommerce requires WooCommerce to be installed and active.', 'store-insights-for-woocommerce' )
        . '</p></div>';
    }
    );

    return;
  }

  if ( is_admin() ) {
    \StoreInsightsWoo\Plugin::init();
  }
} );
