<?php

/**
 * Collects WooCommerce store metrics for widget.
 *
 * @package StorelytWidgetWoo\Data
 */

declare ( strict_types = 1 );

namespace StorelytWidgetWoo\Data;

use StorelytWidgetWoo\Data\MetricsRepository;

defined( 'ABSPATH' ) || exit;

class StoreQuery {
  // Collect and return store metrics.
  public static function get_metrics(): array {
    if ( ! class_exists( 'WooCommerce' ) ) {
      return self::empty_state();
    }

    $now       = time();
    $today     = strtotime( 'today', $now );
    $week_ago  = strtotime( '-7 days', $now );
    $month_ago = strtotime( '-30 days', $now );

    return array(
      'generated_at' => $now,
      'sections'     => array(
        'store_today' => self::store_today( $today, $now ),
        'last_7_days' => self::last_7_days( $week_ago, $now ),
        'best_30'     => self::best_performers( $month_ago, $now ),
        'customers'   => self::customers( $week_ago ),
        'health'      => self::health_signals( $week_ago, $now ),
        'operations'  => self::operational_overview(),
      ),
    );
  }

  // Section builders.
  private static function store_today( int $start, int $end ): array {
    $orders  = MetricsRepository::count_orders( $start, $end );
    $revenue = MetricsRepository::sum_revenue( $start, $end );
    $refunds = MetricsRepository::sum_refunds( $start, $end );

    return array(
      'title'  => __( 'Store Today', 'storelyt-widget-for-woocommerce' ),
      'period' => __( 'Today: 00:00 -> now', 'storelyt-widget-for-woocommerce' ),
      'items'  => array(
        array(
          'label' => __( 'Orders', 'storelyt-widget-for-woocommerce' ),
          'value' => $orders,
        ),
        array(
          'label' => __( 'Revenue', 'storelyt-widget-for-woocommerce' ),
          'value' => wc_price( $revenue ),
        ),
        array(
          'label' => __( 'Refunds', 'storelyt-widget-for-woocommerce' ),
          'value' => wc_price( $refunds ),
        ),
      ),
    );
  }

  private static function last_7_days( int $start, int $end ): array {
    $orders          = MetricsRepository::count_orders( $start, $end );
    $revenue         = MetricsRepository::sum_revenue( $start, $end );
    $avg_order_value = MetricsRepository::avg_order_value( $start, $end );
    $refunds         = MetricsRepository::sum_refunds( $start, $end );

    return array(
      'title'  => __( 'Last 7 Days', 'storelyt-widget-for-woocommerce' ),
      'period' => __( 'Rolling last 7 days', 'storelyt-widget-for-woocommerce' ),
      'items'  => array(
        array(
          'label' => __( 'Orders', 'storelyt-widget-for-woocommerce' ),
          'value' => $orders,
        ),
        array(
          'label' => __( 'Revenue', 'storelyt-widget-for-woocommerce' ),
          'value' => wc_price( $revenue ),
        ),
        array(
          'label' => __( 'Avg order value', 'storelyt-widget-for-woocommerce' ),
          'value' => $avg_order_value,
        ),
        array(
          'label' => __( 'Refunds', 'storelyt-widget-for-woocommerce' ),
          'value' => wc_price( $refunds ),
        ),
      ),
    );
  }

  private static function best_performers( int $start, int $end ): array {
    $best_product        = MetricsRepository::best_product( $start, $end );
    $best_category       = MetricsRepository::best_category( $start, $end );
    $top_coupon          = MetricsRepository::top_coupon( $start, $end );
    $top_payment_method  = MetricsRepository::top_payment_method( $start, $end );
    $top_shipping_method = MetricsRepository::top_shipping_method( $start, $end );

    return array(
      'title'  => __( 'Best Performers', 'storelyt-widget-for-woocommerce' ),
      'period' => __( 'Last 30 days', 'storelyt-widget-for-woocommerce' ),
      'items'  => array(
        array(
          'label' => __( 'Best selling product', 'storelyt-widget-for-woocommerce' ),
          'value' => $best_product,
        ),
        array(
          'label' => __( 'Best category', 'storelyt-widget-for-woocommerce' ),
          'value' => $best_category,
        ),
        array(
          'label' => __( 'Top coupon', 'storelyt-widget-for-woocommerce' ),
          'value' => $top_coupon,
        ),
        array(
          'label' => __( 'Top payment method', 'storelyt-widget-for-woocommerce' ),
          'value' => $top_payment_method,
        ),
        array(
          'label' => __( 'Top shipping method', 'storelyt-widget-for-woocommerce' ),
          'value' => $top_shipping_method,
        ),
      ),
    );
  }

  private static function customers( int $week_ago ): array {
    $new_customers       = MetricsRepository::new_customers( $week_ago );
    $returning_customers = MetricsRepository::returning_customers( $week_ago );
    $total_customers     = MetricsRepository::total_customers();

    return array(
      'title'  => __( 'Customers', 'storelyt-widget-for-woocommerce' ),
      'period' => __( '*registered customers', 'storelyt-widget-for-woocommerce' ),
      'items'  => array(
        array(
          'label' => __( 'New customers (7 days)', 'storelyt-widget-for-woocommerce' ),
          'value' => $new_customers,
        ),
        array(
          'label' => __( 'Returning customers (7 days)', 'storelyt-widget-for-woocommerce' ),
          'value' => $returning_customers,
        ),
        array(
          'label' => __( 'Total customers', 'storelyt-widget-for-woocommerce' ),
          'value' => $total_customers,
        ),
      ),
    );
  }

  private static function health_signals( int $start, int $end ): array {
    $refund_rate   = MetricsRepository::refund_rate( $start, $end );
    $failure_rate  = MetricsRepository::order_failure_rate( $start, $end );
    $revenue_trend = MetricsRepository::revenue_trend( $start, $end );

    return array(
      'title'  => __( 'Quick Health Signals', 'storelyt-widget-for-woocommerce' ),
      'period' => __( 'Last 7 days', 'storelyt-widget-for-woocommerce' ),
      'items'  => array(
        array(
          'label' => __( 'Refund rate', 'storelyt-widget-for-woocommerce' ),
          'value' => $refund_rate,
        ),
        array(
          'label' => __( 'Order failure rate', 'storelyt-widget-for-woocommerce' ),
          'value' => $failure_rate,
        ),
        array(
          'label' => __( 'Revenue trend vs previous week', 'storelyt-widget-for-woocommerce' ),
          'value' => $revenue_trend,
        ),
      ),
    );
  }

  private static function operational_overview(): array {
    $orders_pending_processing = MetricsRepository::orders_pending_processing();
    $orders_on_hold            = MetricsRepository::orders_on_hold();
    $low_stock_products        = MetricsRepository::low_stock_count();

    return array(
      'title'  => __( 'Operational Overview', 'storelyt-widget-for-woocommerce' ),
      'period' => '',
      'items'  => array(
        array(
          'label' => __( 'Orders pending / processing', 'storelyt-widget-for-woocommerce' ),
          'value' => $orders_pending_processing,
        ),
        array(
          'label' => __( 'Orders On-Hold', 'storelyt-widget-for-woocommerce' ),
          'value' => $orders_on_hold,
        ),
        array(
          'label' => __( 'Low stock products', 'storelyt-widget-for-woocommerce' ),
          'value' => $low_stock_products,
        ),
      ),
    );
  }

  // Fallback.
  private static function empty_state(): array {
    return array(
      'generated_at' => time(),
      'sections'     => array(),
    );
  }
}
