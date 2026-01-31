<?php
// Path: src/Data/MetricsRepository.php

/**
 * Low-level WooCommerce metrics queries.
 *
 * @package StorelytWidgetWoo\Data;
 */

declare ( strict_types = 1 );

namespace StorelytWidgetWoo\Data;

defined( 'ABSPATH' ) || exit;

class MetricsRepository {
  public static function count_orders( int $start, int $end ): int {
    $query = new \WC_Order_Query( array(
      'status'       => array( 'wc-completed', 'wc-processing' ),
      'date_created' => gmdate( 'Y-m-d H:i:s', $start ) . '...' . gmdate( 'Y-m-d H:i:s', $end ),
      'return'       => 'ids',
      'limit'        => -1,
    ) );

    return count( $query->get_orders() );
  }

  public static function sum_revenue( int $start, int $end ): float {
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

  public static function sum_refunds( int $start, int $end ): float {
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

  public static function avg_order_value( int $start, int $end ): string {
    $orders  = self::count_orders( $start, $end );
    $revenue = self::sum_revenue( $start, $end );

    return $orders > 0
      ? wc_price( $revenue / $orders )
      : __( 'No data yet', 'storelyt-widget-for-woocommerce' );
  }

  public static function best_product( int $start, int $end ): string {
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
      return __( 'No data yet', 'storelyt-widget-for-woocommerce' );
    }

    arsort( $counts );
    $name = array_key_first( $counts );

    return sprintf( '%s (%d sold)', $name, $counts[ $name ] );
  }

  public static function best_category( int $start, int $end ): string {
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
      return __( 'No data yet', 'storelyt-widget-for-woocommerce' );
    }

    arsort( $counts );
    $cat_id = array_key_first( $counts );
    $term   = get_term( $cat_id, 'product_cat' );

    return $term && ! is_wp_error( $term ) ? $term->name : __( 'Unknown', 'storelyt-widget-for-woocommerce' );
  }

  public static function top_coupon( int $start, int $end ): string {
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
      return __( 'No coupons used', 'storelyt-widget-for-woocommerce' );
    }

    arsort( $coupons );

    return array_key_first( $coupons );
  }

  public static function top_payment_method( int $start, int $end ): string {
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
      return __( 'No data yet', 'storelyt-widget-for-woocommerce' );
    }

    arsort( $methods );

    return array_key_first( $methods );
  }

  public static function top_shipping_method( int $start, int $end ): string {
    $query = new \WC_Order_Query( array(
      'status'       => array( 'wc-completed', 'wc-processing' ),
      'date_created' => gmdate( 'Y-m-d H:i:s', $start ) . '...' . gmdate( 'Y-m-d H:i:s', $end ),
      'return'       => 'objects',
      'limit'        => -1,
    ) );

    $methods = array();

    foreach ( $query->get_orders() as $order ) {
      foreach ( $order->get_shipping_methods() as $shipping ) {
        $label = $shipping->get_name();

        if ( $label ) {
          $methods[ $label ] = ( $methods[ $label ] ?? 0 ) + 1;
        }
      }
    }

    if ( empty( $methods ) ) {
      return __( 'No data yet', 'storelyt-widget-for-woocommerce' );
    }

    arsort( $methods );

    return array_key_first( $methods );
  }

  public static function new_customers( int $start ): int {
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

  public static function returning_customers( int $start ): int {
    $recent_orders = new \WC_Order_Query( array(
      'status'       => array( 'wc-completed', 'wc-processing' ),
      'date_created' => '>' . gmdate( 'Y-m-d H:i:s', $start ),
      'return'       => 'objects',
      'limit'        => -1,
    ) );

    $customers_ids = array();

    foreach ( $recent_orders->get_orders() as $order ) {
      $customer_id = (int) $order->get_customer_id();

      if ( $customer_id > 0 ) {
        $customers_ids[ $customer_id ] = true;
      }
    }

    if ( empty( $customers_ids ) ) {
      return 0;
    }

    $returning = 0;

    foreach ( array_keys( $customers_ids ) as $customer_id ) {
      $past_orders = new \WC_Order_Query( array(
        'customer_id'  => $customer_id,
        'status'       => array( 'wc-completed', 'wc-processing' ),
        'date_created' => '<' . gmdate( 'Y-m-d H:i:s', $start ),
        'limit'        => 1,
        'return'       => 'ids',
      ) );

      if ( ! empty( $past_orders->get_orders() ) ) {
        ++$returning;
      }
    }

    return $returning;
  }

  public static function total_customers(): int {
    $users = get_users( array(
      'role'   => 'customer',
      'fields' => 'ID',
    ) );

    return is_array( $users ) ? count( $users ) : 0;
  }

  public static function refund_rate( int $start, int $end ): string {
    $orders  = self::count_orders( $start, $end );
    $refunds = self::sum_refunds( $start, $end );

    if ( $orders <= 0 ) {
      return __( 'No data yet', 'storelyt-widget-for-woocommerce' );
    }

    $rate = ( $refunds > 0 ) ? round( ( $refunds / max( 1, self::sum_revenue( $start, $end ) ) ) * 100, 2 ) : 0;

    return $rate . '%';
  }

  public static function order_failure_rate( int $start, int $end ): string {
    $total_query = new \WC_Order_Query( array(
      'status'       => array( 'wc-completed', 'wc-processing', 'wc-failed' ),
      'date_created' => gmdate( 'Y-m-d H:i:s', $start ) . '...' . gmdate( 'Y-m-d H:i:s', $end ),
      'return'       => 'ids',
      'limit'        => -1,
    ) );

    $total = count( $total_query->get_orders() );

    if ( 0 === $total ) {
      return __( 'No data yet', 'storelyt-widget-for-woocommerce' );
    }

    $failed_query = new \WC_Order_Query( array(
      'status'       => array( 'wc-failed' ),
      'date_created' => gmdate( 'Y-m-d H:i:s', $start ) . '...' . gmdate( 'Y-m-d H:i:s', $end ),
      'return'       => 'ids',
      'limit'        => -1,
    ) );

    $failed = count( $failed_query->get_orders() );

    $rate = round( ( $failed / $total ) * 100, 2 );

    return $rate . '%';
  }

  public static function revenue_trend( int $start, int $end ): string {
    $two_weeks = strtotime( '-14 days', $end );

    $current  = self::sum_revenue( $start, $end );
    $previous = self::sum_revenue( $two_weeks, $start );

    if ( $previous <= 0 ) {
      return __( 'No previous week data', 'storelyt-widget-for-woocommerce' );
    }

    $diff = ( ( $current - $previous ) / $previous ) * 100;
    $diff = round( $diff, 1 );

    $sign = $diff > 0 ? '+' : '';

    return sprintf(
      __( '%s%% vs previous week', 'storelyt-widget-for-woocommerce' ),
      $sign . $diff
    );
  }

  public static function orders_pending_processing(): int {
    $query = new \WC_Order_Query( array(
      'status' => array( 'wc-pending', 'wc-processing' ),
      'return' => 'ids',
      'limit'  => -1,
    ) );

    return count( $query->get_orders() );
  }

  public static function orders_on_hold(): int {
    $query = new \WC_Order_Query( array(
      'status' => array( 'wc-on-hold' ),
      'return' => 'ids',
      'limit'  => -1,
    ) );

    return count( $query->get_orders() );
  }

  public static function low_stock_count(): int {
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
