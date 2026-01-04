<?php

/**
 * Registers and displays the Store Insights for WooCommerce dashboard widget.
 *
 * @package StoreInsightsWoo\Dashboard
 */

declare ( strict_types = 1 );

namespace StoreInsightsWoo\Dashboard;

use StoreInsightsWoo\Data\InsightsCache;
use StoreInsightsWoo\Data\StoreQuery;

defined( 'ABSPATH' ) || exit;

class DashboardWidget {

  public static function init(): void {
    add_action( 'wp_dashboard_setup', array( self::class, 'register_widget' ) );
  }

  public static function register_widget(): void {
    wp_add_dashboard_widget(
      'store_insights_woo_widget',
      __( 'Store Insights', 'store-insights-for-woocommerce' ),
      array( self::class, 'render_widget' )
    );
  }

  // Render the widget output.
  public static function render_widget(): void {

    $cache = InsightsCache::get();

// Cache miss or expired -> regenerate once
    if ( empty( $cache ) || ! empty( $cache[ 'is_expired' ] ) ) {
      $data = StoreQuery::get_metrics();
      InsightsCache::set( $data );
      $cache = InsightsCache::get();
    }

    echo '<div class="siw-dashboard-widget">';

    if ( ! empty( $cache[ 'sections' ] ) ) {
      echo '<div class="siw-insights-grid">';

      foreach ( $cache[ 'sections' ] as $section ) {
        echo '<div class="siw-section">';
        echo '<div class="siw-section-title">';
        echo '<h4>' . esc_html( $section[ 'title' ] ) . '</h4>';

        if ( ! empty( $section[ 'period' ] ) ) {
          echo '<span>(' . esc_html( $section[ 'period' ] ) . ')</span>';
        }

        echo '</div>';

        foreach ( $section[ 'items' ] as $item ) {
          $value = $item[ 'value' ];

          if ( '' === $value || null === $value ) {
            $value = __( 'No data yet', 'store-insights-for-woocommerce' );
          }

          echo '<div class="siw-item">';
          echo '<h5>' . esc_html( $item[ 'label' ] ) . ':</h5>';
          echo '<p>' . wp_kses_post( $value ) . '</p>';
          echo '</div>';
        }

        echo '</div>'; // .siw-section
      }

      echo '</div>'; // .siw-insights-grid
    } else {
      echo '<p class="siw-empty">' . esc_html__( 'No insights available yet.', 'store-insights-for-woocommerce' ) . '</p>';
    }

// Footer
    if ( ! empty( $cache[ 'generated_at' ] ) ) {
      echo '<div class="siw-footer">';
      echo '<span class="siw-meta">';
      echo esc_html__( 'Last updated: ', 'store-insights-for-woocommerce' );
      echo esc_html( InsightsCache::format_local_time( (int) $cache[ 'generated_at' ] ) );
      echo '</span>';
      echo '<span class="siw-meta">';
      echo esc_html__( 'Auto-refreshes on dashboard view (10 min cache).', 'store-insights-for-woocommerce' );
      echo '</span>';
      echo '</div>';
    }

    echo '</div>'; // siw-dashboard-widget
  }
}
