<?php

/**
 * Handles storing and retrieving insights cache with TTL (10 min).
 *
 * @package StorePulseWoo\Data
 */

declare ( strict_types = 1 );

namespace StorePulseWoo\Data;

defined( 'ABSPATH' ) || exit;

class InsightsCache {

  // Option name in wp_options.
  private const OPTION = 'storepulse_insights_widget';
  // Cache TTL = 10 minutes.
  private const TTL = 600;

  // Get cached data if valid, otherwise null.
  public static function get(): ?array {

    $cache = get_option( self::OPTION, array() );

    if ( empty( $cache[ 'sections' ] ) || empty( $cache[ 'generated_at' ] ) ) {
      return null;
    }

    // Attach expiration info instead of returning null.
    $timestamp             = (int) $cache[ 'generated_at' ];
    $cache[ 'is_expired' ] = self::is_expired( $timestamp );

    return $cache;
  }

  // Save new insights with metadata.
  public static function set( array $data ): void {

    if ( empty( $data ) ) {
      return;
    }

    $cache = array(
      'generated_at' => time(),
      'sections'     => $data[ 'sections' ] ?? array(),
    );

    update_option( self::OPTION, $cache );
  }

  // Check if cache expired.
  public static function is_expired( int $timestamp ): bool {
    return ( time() - $timestamp ) > self::TTL;
  }

  // Convert UTC timestamp to local site time respecting timezone settings.
  public static function format_local_time( int $timestamp ): string {

    $timezone = wp_timezone();
    $fmt      = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
    $dt       = new \DateTime( 'now', $timezone );
    $dt->setTimestamp( $timestamp );

    return $dt->format( $fmt );
  }
}
