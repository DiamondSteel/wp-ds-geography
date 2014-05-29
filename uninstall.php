<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit(); }

global $wpdb;

$table_markers               = $wpdb->prefix.'wpds_geography_markers';
$table_layers                = $wpdb->prefix.'wpds_geography_layers';
$table_layers_relationships  = $wpdb->prefix.'wpds_geography_layers_relationships';

$wpdb->query( "DROP TABLE IF EXISTS $table_markers" );
$wpdb->query( "DROP TABLE IF EXISTS $table_layers" );
$wpdb->query( "DROP TABLE IF EXISTS $table_layers_relationships" );

delete_option( 'wpds_geography_options_permissions' );
delete_option( 'wpds_geography_options_maps' );
delete_option( 'wpds_geography_options_map_source' );
delete_option( 'wpds_geography_options_popup' );
delete_option( 'wpds_geography_options_markers' );
delete_option( 'wpds_geography_options_layers' );

?>