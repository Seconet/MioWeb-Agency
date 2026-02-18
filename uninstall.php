<?php
/**
 * File di disinstallazione del plugin.
 */

// 1. Controllo di sicurezza: se la costante non è definita, esci immediatamente.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


global $wpdb;

// 2. Eliminazione Tabelle Custom
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mioweb_clienti" );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mioweb_hosting" ); //wp_mioweb_hosting
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mioweb_manutenzioni" );

// 4. Eliminazione Custom Post Types (Siti, Plugin, Temi)
// Usiamo i nomi esatti che hai nel database
$mioweb_post_types = [ 'mioweb_sito', 'mioweb_plugin', 'mioweb_tema' ];

foreach ( $mioweb_post_types as $type ) {
   $posts = get_posts( [
        'post_type'   => $type,
        'numberposts' => -1,
        'post_status' => 'any',
        'fields'      => 'ids',
    ] );

    if ( $posts  ) {
        foreach ( $posts as $post_id ) {
            wp_delete_post( $post_id, true );
        }
    }
}

// 5. Pulizia finale Opzioni (se presenti)
delete_option( 'mioweb_agency_version' );
delete_option( 'mioweb_db_version' );