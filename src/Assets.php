<?php

/**
 * Handles loading of assets.
 *
 * @package StorePulseWoo
 */

declare ( strict_types = 1 );

namespace StorePulseWoo;

defined( 'ABSPATH' ) || exit;

class Assets {

  public function enqueue_admin_assets(): void {
    $screen = get_current_screen();

    if ( ! $screen || ( 'dashboard' !== $screen->base ) ) {
      return;
    }

    wp_enqueue_style(
      'siw-admin',
      STOREPULSEWOO_URL . 'assets/css/admin.css',
      array(),
      STOREPULSEWOO_VERSION
    );

    wp_enqueue_style( 'dashicons' );
  }
}
