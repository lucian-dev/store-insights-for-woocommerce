<?php

/**
 * Main plugin bootstrap class.
 * Handles initialization, hooks, and component loading.
 *
 * @package StoreInsightsWoo
 */

declare ( strict_types = 1 );

namespace StoreInsightsWoo;

use StoreInsightsWoo\Assets;
use StoreInsightsWoo\Dashboard\DashboardWidget;

defined( 'ABSPATH' ) || exit;

class Plugin {
  // @var ?Plugin Singleton instance.
  private static ?Plugin $instance = null;

  // Get singleton instance.
  public static function init(): self {

    if ( null === self::$instance ) {
      self::$instance = new self();
      self::$instance->hooks();
    }

    return self::$instance;
  }

  // Register all plugin hooks and components.
  private function hooks(): void {

    // Register dashboard widget.
    DashboardWidget::init();

    // Load admin assets.
    add_action( 'admin_enqueue_scripts', array( new Assets(), 'enqueue_admin_assets' ) );
  }

  // Prevent direct object creation.
  private function __construct() {}

  // Prevent cloning.
  private function __clone(): void {}

  // Prevent unserialization.
  public function __wakeup(): void {
    throw new \Exception( 'Cannot unserialize singleton' );
  }
}
