<?php

/**
 * Handles loading of assets.
 *
 * @package StorelytWidgetWoo
 */

declare ( strict_types = 1 );

namespace StorelytWidgetWoo;

defined( 'ABSPATH' ) || exit;

class Assets {

  public function enqueue_admin_assets(): void {
    $screen = get_current_screen();

    if ( ! $screen || ( 'dashboard' !== $screen->base ) ) {
      return;
    }

    wp_enqueue_style(
      'siw-admin',
      STORELYTWIDGETWOO_URL . 'assets/css/admin.css',
      array(),
      STORELYTWIDGETWOO_VERSION
    );

    wp_enqueue_style( 'dashicons' );
  }
}
