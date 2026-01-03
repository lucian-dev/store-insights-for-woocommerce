<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
  exit;
}

// Clear stored cache.
delete_option( 'store_insights_widget' );
