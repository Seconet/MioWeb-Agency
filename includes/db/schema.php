<?php
/**
 * Database Schema
 *
 * @package MioWebAgency
 */

// Evita accesso diretto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mioweb_create_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Prefisso tabelle
    $table_clienti = $wpdb->prefix . 'mioweb_clienti';
    $table_hosting = $wpdb->prefix . 'mioweb_hosting';
    $table_manutenzioni = $wpdb->prefix . 'mioweb_manutenzioni';
    
    // SQL per tabella clienti
    $sql_clienti = "CREATE TABLE IF NOT EXISTS $table_clienti (
        id int(11) NOT NULL AUTO_INCREMENT,
        tipo enum('azienda','privato','ente','associazione') DEFAULT 'privato',
        ragione_sociale varchar(200),
        nome varchar(100),
        cognome varchar(100),
        piva varchar(20),
        cf varchar(20),
        email varchar(100) NOT NULL,
        pec varchar(100),
        telefono varchar(20),
        cellulare varchar(20),
        indirizzo varchar(255),
        citta varchar(100),
        cap varchar(10),
        provincia varchar(2),
        nazione varchar(2) DEFAULT 'IT',
        note text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY piva (piva),
        KEY email (email),
        KEY ragione_sociale (ragione_sociale)
    ) $charset_collate;";
    
    // SQL per tabella hosting
    $sql_hosting = "CREATE TABLE IF NOT EXISTS $table_hosting (
        id int(11) NOT NULL AUTO_INCREMENT,
        cliente_id int(11) NOT NULL,
        nome_sito varchar(100) NOT NULL,
        dominio_principale varchar(255),
        provider varchar(100),
        piano varchar(100),
        data_attivazione date,
        data_scadenza date,
        costo decimal(10,2),
        valuta varchar(3) DEFAULT 'EUR',
        ciclo_fatturazione enum('mensile','trimestrale','semestrale','annuale','biennale') DEFAULT 'annuale',
        credenziali_ftp text,
        credenziali_admin text,
        ip_server varchar(45),
        nameserver1 varchar(255),
        nameserver2 varchar(255),
        note_tecniche text,
        status enum('attivo','sospeso','cancellato','in_attivazione') DEFAULT 'attivo',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY cliente_id (cliente_id),
        KEY data_scadenza (data_scadenza),
        KEY status (status)
    ) $charset_collate;";
    
    // SQL per tabella manutenzioni
    $sql_manutenzioni = "CREATE TABLE IF NOT EXISTS $table_manutenzioni (
        id int(11) NOT NULL AUTO_INCREMENT,
        cliente_id int(11) NOT NULL,
        hosting_id int(11),
        sito_id bigint(20),
        tipo enum('base','professional','premium','custom') DEFAULT 'base',
        nome_contratto varchar(200),
        data_inizio date NOT NULL,
        data_fine date,
        prossimo_rinnovo date,
        importo decimal(10,2),
        valuta varchar(3) DEFAULT 'EUR',
        ciclodi_rinnovo enum('mensile','trimestrale','semestrale','annuale') DEFAULT 'annuale',
        stato enum('attivo','in_scadenza','scaduto','sospeso','cancellato') DEFAULT 'attivo',
        note text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY cliente_id (cliente_id),
        KEY hosting_id (hosting_id),
        KEY sito_id (sito_id),
        KEY prossimo_rinnovo (prossimo_rinnovo),
        KEY stato (stato)
    ) $charset_collate;";
    
    // Includi file per dbDelta
    if ( ! function_exists('dbDelta') ) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    }
    
    // Esegui creazione tabelle
    dbDelta( $sql_clienti );
    dbDelta( $sql_hosting );
    dbDelta( $sql_manutenzioni );
    
    // Salva versione del db per future migrazioni
    add_option( 'mioweb_db_version', '1.0.0' );
}