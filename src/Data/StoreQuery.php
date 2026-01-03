<?php
// Path: src/Data/StoreQuery.php

/**
 * Collects WooCommerce store metrics for widget.
 *
 * @package StoreInsightsWoo\Data
 */

declare ( strict_types = 1 );

namespace StoreInsightsWoo\Data;

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
    $orders  = self::count_orders( $start, $end );
    $revenue = self::sum_revenue( $start, $end );
    $refunds = self::sum_refunds( $start, $end );

    return array(
      'title'  => __( 'Store Today', 'store-insights-for-woocommerce' ),
      'period' => __( 'Today (00:00 -> now)', 'store-insights-for-woocommerce' ),
      'items'  => array(
        array(
          'label' => __( 'Orders', 'store-insights-for-woocommerce' ),
          'value' => $orders,
        ),
        array(
          'label' => __( 'Revenue', 'store-insights-for-woocommerce' ),
          'value' => wc_price( $revenue ),
        ),
        array(
          'label' => __( 'Refunds', 'store-insights-for-woocommerce' ),
          'value' => wc_price( $refunds ),
        ),
      ),
    );
  }

  private static function last_7_days( int $start, int $end ): array {
    $orders          = self::count_orders( $start, $end );
    $revenue         = self::sum_revenue( $start, $end );
    $avg_order_value = self::avg_order_value( $start, $end );
    $refunds         = self::sum_refunds( $start, $end );

    return array(
      'title'  => __( 'Last 7 Days', 'store-insights-for-woocommerce' ),
      'period' => __( 'Rolling last 7 days', 'store-insights-for-woocommerce' ),
      'items'  => array(
        array(
          'label' => __( 'Orders', 'store-insights-for-woocommerce' ),
          'value' => $orders,
        ),
        array(
          'label' => __( 'Revenue', 'store-insights-for-woocommerce' ),
          'value' => wc_price( $revenue ),
        ),
        array(
          'label' => __( 'Avg order value', 'store-insights-for-woocommerce' ),
          'value' => $avg_order_value,
        ),
        array(
          'label' => __( 'Refunds', 'store-insights-for-woocommerce' ),
          'value' => wc_price( $refunds ),
        ),
      ),
    );
  }

  private static function best_performers( int $start, int $end ): array {
    $best_product       = self::best_product( $start, $end );
    $best_category      = self::best_category( $start, $end );
    $top_coupon         = self::top_coupon( $start, $end );
    $top_payment_method = self::top_payment_method( $start, $end );

    return array(
      'title'  => __( 'Best Performers', 'store-insights-for-woocommerce' ),
      'period' => __( 'Last 30 days', 'store-insights-for-woocommerce' ),
      'items'  => array(
        array(
          'label' => __( 'Best selling product', 'store-insights-for-woocommerce' ),
          'value' => $best_product,
        ),
        array(
          'label' => __( 'Best category', 'store-insights-for-woocommerce' ),
          'value' => $best_category,
        ),
        array(
          'label' => __( 'Top coupon', 'store-insights-for-woocommerce' ),
          'value' => $top_coupon,
        ),
        array(
          'label' => __( 'Top payment method', 'store-insights-for-woocommerce' ),
          'value' => $top_payment_method,
        ),
      ),
    );
  }

  private static function customers( int $week_ago ): array {
    $new_customers   = self::new_customers( $week_ago );
    $total_customers = self::total_customers();

    return array(
      'title'  => __( 'Customers', 'store-insights-for-woocommerce' ),
      'period' => __( '*registered customers', 'store-insights-for-woocommerce' ),
      'items'  => array(
        array(
          'label' => __( 'New customers (7 days)', 'store-insights-for-woocommerce' ),
          'value' => $new_customers,
        ),
        array(
          'label' => __( 'Total customers', 'store-insights-for-woocommerce' ),
          'value' => $total_customers,
        ),
      ),
    );
  }

  private static function health_signals( int $start, int $end ): array {
    $refund_rate   = self::refund_rate( $start, $end );
    $revenue_trend = self::revenue_trend( $start, $end );

    return array(
      'title'  => __( 'Quick Health Signals', 'store-insights-for-woocommerce' ),
      'period' => __( 'Last 7 days', 'store-insights-for-woocommerce' ),
      'items'  => array(
        array(
          'label' => __( 'Refund rate', 'store-insights-for-woocommerce' ),
          'value' => $refund_rate,
        ),
        array(
          'label' => __( 'Revenue trend vs previous week', 'store-insights-for-woocommerce' ),
          'value' => $revenue_trend,
        ),
      ),
    );
  }

  private static function operational_overview(): array {
    $orders_pending_processing = self::orders_pending_processing();
    $low_stock_products        = self::low_stock_count();

    return array(
      'title'  => __( 'Operational Overview', 'store-insights-for-woocommerce' ),
      'period' => __( 'Mixed periods', 'store-insights-for-woocommerce' ),
      'items'  => array(
        array(
          'label' => __( 'Orders pending / processing', 'store-insights-for-woocommerce' ),
          'value' => $orders_pending_processing,
        ),
        array(
          'label' => __( 'Low stock products', 'store-insights-for-woocommerce' ),
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

  // Helper query methods.
  private static function count_orders( int $start, int $end ): int {
    $query = new \WC_Order_Query( array(
      'status'       => array( 'wc-completed', 'wc-processing' ),
      'date_created' => gmdate( 'Y-m-d H:i:s', $start ) . '...' . gmdate( 'Y-m-d H:i:s', $end ),
      'return'       => 'ids',
      'limit'        => -1,
    ) );

    return count( $query->get_orders() );
  }

  private static function sum_revenue( int $start, int $end ): float {
    $query = new \WC_Order_Query( array(
      'status'       => array( 'wc-completed', 'wc-processing' ),
      'date_created' => gmdate( 'Y-m-d H:i:s', $start ) . '...' . gmdate( 'Y-m-d H:i:s', $end ),
      'return'       => 'objects',
      'limit'        => -1,
    ) );

    $sum = 0.0;

    foreach ( $query->get_orders() as $order ) {
      $sum += (float) $order->get_total();
    }

    return round( $sum, 2 );
  }

  private static function sum_refunds( int $start, int $end ): float {
    $query = new \WC_Order_Query( array(
      'status'       => array( 'wc-refunded' ),
      'date_created' => gmdate( 'Y-m-d H:i:s', $start ) . '...' . gmdate( 'Y-m-d H:i:s', $end ),
      'return'       => 'objects',
      'limit'        => -1,
    ) );

    $sum = 0.0;

    foreach ( $query->get_orders() as $order ) {
      foreach ( $order->get_refunds() as $refund ) {
        $sum += abs( (float) $refund->get_amount() );
      }
    }

    return round( $sum, 2 );
  }

  private static function avg_order_value( int $start, int $end ): string {
    $orders  = self::count_orders( $start, $end );
    $revenue = self::sum_revenue( $start, $end );

    return $orders > 0
      ? wc_price( $revenue / $orders )
      : __( 'No data yet', 'store-insights-for-woocommerce' );
  }

  private static function best_product( int $start, int $end ): string {
    $query = new \WC_Order_Query( array(
      'status'       => array( 'wc-completed', 'wc-processing' ),
      'date_created' => gmdate( 'Y-m-d H:i:s', $start ) . '...' . gmdate( 'Y-m-d H:i:s', $end ),
      'return'       => 'objects',
      'limit'        => -1,
    ) );

    $counts = array();

    foreach ( $query->get_orders() as $order ) {
      foreach ( $order->get_items() as $item ) {
        $name            = $item->get_name();
        $qty             = (int) $item->get_quantity();
        $counts[ $name ] = ( $counts[ $name ] ?? 0 ) + $qty;
      }
    }

    if ( empty( $counts ) ) {
      return __( 'No data yet', 'store-insights-for-woocommerce' );
    }

    arsort( $counts );
    $name = array_key_first( $counts );

    return sprintf( '%s (%d sold)', $name, $counts[ $name ] );
  }

  private static function best_category( int $start, int $end ): string {
    $query = new \WC_Order_Query( array(
      'status'       => array( 'wc-completed', 'wc-processing' ),
      'date_created' => gmdate( 'Y-m-d H:i:s', $start ) . '...' . gmdate( 'Y-m-d H:i:s', $end ),
      'return'       => 'objects',
      'limit'        => -1,
    ) );

    $counts = array();

    foreach ( $query->get_orders() as $order ) {
      foreach ( $order->get_items() as $item ) {
        $product = $item->get_product();

        if ( ! $product ) {
          continue;
        }

        foreach ( $product->get_category_ids() as $cat_id ) {
          $counts[ $cat_id ] = ( $counts[ $cat_id ] ?? 0 ) + (int) $item->get_quantity();
        }
      }
    }

    if ( empty( $counts ) ) {
      return __( 'No data yet', 'store-insights-for-woocommerce' );
    }

    arsort( $counts );
    $cat_id = array_key_first( $counts );
    $term   = get_term( $cat_id, 'product_cat' );

    return $term && ! is_wp_error( $term ) ? $term->name : __( 'Unknown', 'store-insights-for-woocommerce' );
  }

  private static function top_coupon( int $start, int $end ): string {
    $query = new \WC_Order_Query( array(
      'status'       => array( 'wc-completed', 'wc-processing' ),
      'date_created' => gmdate( 'Y-m-d H:i:s', $start ) . '...' . gmdate( 'Y-m-d H:i:s', $end ),
      'return'       => 'objects',
      'limit'        => -1,
    ) );

    $coupons = array();

    foreach ( $query->get_orders() as $order ) {
      foreach ( $order->get_coupon_codes() as $code ) {
        $coupons[ $code ] = ( $coupons[ $code ] ?? 0 ) + 1;
      }
    }

    if ( empty( $coupons ) ) {
      return __( 'No coupons used', 'store-insights-for-woocommerce' );
    }

    arsort( $coupons );

    return array_key_first( $coupons );
  }

  private static function top_payment_method( int $start, int $end ): string {
    $query = new \WC_Order_Query( array(
      'status'       => array( 'wc-completed', 'wc-processing' ),
      'date_created' => gmdate( 'Y-m-d H:i:s', $start ) . '...' . gmdate( 'Y-m-d H:i:s', $end ),
      'return'       => 'objects',
      'limit'        => -1,
    ) );

    $methods = array();

    foreach ( $query->get_orders() as $order ) {
      $method = $order->get_payment_method_title();

      if ( $method ) {
        $methods[ $method ] = ( $methods[ $method ] ?? 0 ) + 1;
      }
    }

    if ( empty( $methods ) ) {
      return __( 'No data yet', 'store-insights-for-woocommerce' );
    }

    arsort( $methods );

    return array_key_first( $methods );
  }

  private static function new_customers( int $start ): int {
    $users = get_users( array(
      'role'       => 'customer',
      'fields'     => 'ID',
      'date_query' => array(
        array(
          'after'     => gmdate( 'Y-m-d H:i:s', $start ),
          'inclusive' => true,
        ),
      ),
    ) );

    return is_array( $users ) ? count( $users ) : 0;
  }

  private static function total_customers(): int {
    $users = get_users( array(
      'role'   => 'customer',
      'fields' => 'ID',
    ) );

    return is_array( $users ) ? count( $users ) : 0;
  }

  private static function refund_rate( int $start, int $end ): string {
    $orders  = self::count_orders( $start, $end );
    $refunds = self::sum_refunds( $start, $end );

    if ( $orders <= 0 ) {
      return __( 'No data yet', 'store-insights-for-woocommerce' );
    }

    $rate = ( $refunds > 0 ) ? round( ( $refunds / max( 1, self::sum_revenue( $start, $end ) ) ) * 100, 2 ) : 0;

    return $rate . '%';
  }

  private static function revenue_trend( int $start, int $end ): string {

    $two_weeks = strtotime( '-14 days', $end );

    $current  = self::sum_revenue( $start, $end );
    $previous = self::sum_revenue( $two_weeks, $start );

    if ( $previous <= 0 ) {
      return __( 'No previous week data', 'store-insights-for-woocommerce' );
    }

    $diff = ( ( $current - $previous ) / $previous ) * 100;
    $diff = round( $diff, 1 );

    $sign = $diff > 0 ? '+' : '';

    return sprintf(
      __( '%s%% vs previous week', 'store-insights-for-woocommerce' ),
      $sign . $diff
    );
  }

  private static function orders_pending_processing(): int {
    $query = new \WC_Order_Query( array(
      'status' => array( 'wc-pending', 'wc-processing' ),
      'return' => 'ids',
      'limit'  => -1,
    ) );

    return count( $query->get_orders() );
  }

  private static function low_stock_count(): int {

    if ( ! function_exists( 'wc_get_low_stock_amount' ) ) {
      return 0;
    }

    $args = array(
      'status'    => 'publish',
      'limit'     => -1,
      'low_stock' => true,
      'return'    => 'ids',
    );

    return count( wc_get_products( $args ) );
  }
}
