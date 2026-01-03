<?php

/**
 * Handles loading of assets.
 *
 * @package StoreInsightsWoo
 */

declare ( strict_types = 1 );

namespace StoreInsightsWoo;

defined( 'ABSPATH' ) || exit;

class Assets {

  public function enqueue_admin_assets(): void {
    $screen = get_current_screen();

    if ( ! $screen || ( 'dashboard' !== $screen->base ) ) {
      return;
    }

    wp_enqueue_style(
      'siw-admin',
      STOREINSIGHTSWOO_URL . 'assets/css/admin.css',
      array(),
      STOREINSIGHTSWOO_VERSION
    );

    wp_enqueue_style( 'dashicons' );
  }
}
