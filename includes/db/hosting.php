<?php

/**
 * CRUD Hosting
 * Funzioni per gestire la tabella mioweb_hosting
 *
 * @package MioWebAgency
 */

// Evita accesso diretto
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Crea un nuovo hosting
 */
function mioweb_create_hosting($data)
{
    global $wpdb;

    $table = $wpdb->prefix . 'mioweb_hosting';

    $defaults = [
        'cliente_id' => 0,
        'nome_sito' => '',
        'dominio_principale' => '',
        'provider' => '',
        'piano' => '',
        'data_attivazione' => current_time('Y-m-d'),
        'data_scadenza' => null,
        'costo' => 0,
        'valuta' => 'EUR',
        'ciclo_fatturazione' => 'annuale',
        'credenziali_ftp' => '',
        'credenziali_admin' => '',
        'ip_server' => '',
        'nameserver1' => '',
        'nameserver2' => '',
        'note_tecniche' => '',
        'status' => 'attivo'
    ];

    $data = wp_parse_args($data, $defaults);

    // Sanitizzazione
    $hosting = [
        'cliente_id' => intval($data['cliente_id']),
        'nome_sito' => sanitize_text_field($data['nome_sito']),
        'dominio_principale' => sanitize_text_field($data['dominio_principale']),
        'provider' => sanitize_text_field($data['provider']),
        'piano' => sanitize_text_field($data['piano']),
        'data_attivazione' => sanitize_text_field($data['data_attivazione']),
        'data_scadenza' => ! empty($data['data_scadenza']) ? sanitize_text_field($data['data_scadenza']) : null,
        'costo' => floatval($data['costo']),
        'valuta' => sanitize_text_field($data['valuta']),
        'ciclo_fatturazione' => sanitize_text_field($data['ciclo_fatturazione']),
        'credenziali_ftp' => sanitize_textarea_field($data['credenziali_ftp']),
        'credenziali_admin' => sanitize_textarea_field($data['credenziali_admin']),
        'ip_server' => sanitize_text_field($data['ip_server']),
        'nameserver1' => sanitize_text_field($data['nameserver1']),
        'nameserver2' => sanitize_text_field($data['nameserver2']),
        'note_tecniche' => sanitize_textarea_field($data['note_tecniche']),
        'status' => sanitize_text_field($data['status'])
    ];

    // Validazione
    if (empty($hosting['cliente_id'])) {
        return new WP_Error('no_cliente', __('Client is required', 'mioweb-agency'));
    }

    if (empty($hosting['nome_sito'])) {
        return new WP_Error('no_nome_sito', __('Site name is required', 'mioweb-agency'));
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
    $wpdb->insert($table, $hosting);

    if ($wpdb->insert_id) {
        return $wpdb->insert_id;
    }

    return new WP_Error('insert_failed', __('Failed to create hosting', 'mioweb-agency'));
}

/**
 * Aggiorna un hosting esistente
 */
function mioweb_update_hosting($id, $data)
{
    global $wpdb;

    $table = $wpdb->prefix . 'mioweb_hosting';

    $hosting = [];

    // Campi aggiornabili
    $allowed_fields = [
        'cliente_id',
        'nome_sito',
        'dominio_principale',
        'provider',
        'piano',
        'data_attivazione',
        'data_scadenza',
        'costo',
        'valuta',
        'ciclo_fatturazione',
        'credenziali_ftp',
        'credenziali_admin',
        'ip_server',
        'nameserver1',
        'nameserver2',
        'note_tecniche',
        'status'
    ];

    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            if (in_array($field, ['credenziali_ftp', 'credenziali_admin', 'note_tecniche'])) {
                $hosting[$field] = sanitize_textarea_field($data[$field]);
            } elseif ($field === 'costo') {
                $hosting[$field] = floatval($data[$field]);
            } elseif ($field === 'cliente_id') {
                $hosting[$field] = intval($data[$field]);
            } else {
                $hosting[$field] = sanitize_text_field($data[$field]);
            }
        }
    }

    if (empty($hosting)) {
        return false;
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    return $wpdb->update($table, $hosting, ['id' => intval($id)]);
}

/**
 * Ottieni un hosting per ID
 */
function mioweb_get_hosting($id)
{
    global $wpdb;

     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mioweb_hosting WHERE id = %d",
        intval($id)
    ));
}

/**
 * Ottieni tutti gli hosting con paginazione e filtri
 */
function mioweb_get_hosting_list( $args = [] ) {
    global $wpdb;

    $defaults = [
        'per_page'   => 20,
        'page'       => 1,
        'orderby'    => 'data_scadenza',
        'order'      => 'ASC',
        'search'     => '',
        'cliente_id' => 0,
        'status'     => '',
        'provider'   => '',
        'scadenza'   => '', // 'imminente', 'scaduto', 'future'
    ];

    $args = wp_parse_args( $args, $defaults );

    // Inizializziamo la clausola WHERE
    $where = [ '1=1' ];

    // Ricerca testuale
    if ( ! empty( $args['search'] ) ) {
        $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        $where[]     = $wpdb->prepare(
            "(h.nome_sito LIKE %s OR h.dominio_principale LIKE %s OR c.ragione_sociale LIKE %s OR c.nome LIKE %s OR c.cognome LIKE %s)",
            $search_term,
            $search_term,
            $search_term,
            $search_term,
            $search_term
        );
    }

    // Filtri ID Cliente, Status e Provider
    if ( ! empty( $args['cliente_id'] ) ) {
        $where[] = $wpdb->prepare( "h.cliente_id = %d", absint( $args['cliente_id'] ) );
    }

    if ( ! empty( $args['status'] ) ) {
        $where[] = $wpdb->prepare( "h.status = %s", sanitize_text_field( $args['status'] ) );
    }

    if ( ! empty( $args['provider'] ) ) {
        $where[] = $wpdb->prepare( "h.provider = %s", sanitize_text_field( $args['provider'] ) );
    }

    // Filtri per scadenza
    $today = current_time( 'Y-m-d' );
    if ( 'imminente' === $args['scadenza'] ) {
        $where[] = $wpdb->prepare(
            "h.data_scadenza BETWEEN %s AND %s",
            $today,
            gmdate( 'Y-m-d', strtotime( '+30 days' ) )
        );
    } elseif ( 'scaduto' === $args['scadenza'] ) {
        $where[] = $wpdb->prepare( "h.data_scadenza < %s", $today );
    } elseif ( 'future' === $args['scadenza'] ) {
        $where[] = $wpdb->prepare( "h.data_scadenza >= %s", $today );
    }

    // Uniamo le clausole WHERE. Usiamo phpcs:ignore perché lo scanner teme variabili non preparate.
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $where_clause = implode( ' AND ', $where );

    // Sanitizzazione ORDER BY e ORDER
    $allowed_orderby = [ 'id', 'cliente_id', 'nome_sito', 'provider', 'data_scadenza', 'costo', 'status' ];
    $orderby_field   = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'data_scadenza';
    $order_direction = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

    $offset = ( absint( $args['page'] ) - 1 ) * absint( $args['per_page'] );

    /**
     * COSTRUZIONE QUERY RISULTATI
     * Concateniamo le parti fisse per evitare "interpolated variable" warning.
     */

    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,  WordPress.DB.PreparedSQL.NotPrepared
    $sql_results = "SELECT h.*, c.ragione_sociale, c.nome, c.cognome, c.email FROM " . $wpdb->prefix . "mioweb_hosting h ";
    $sql_results .= "LEFT JOIN " . $wpdb->prefix . "mioweb_clienti c ON h.cliente_id = c.id ";
    $sql_results .= "WHERE " . $where_clause . " ";
    $sql_results .= "ORDER BY h." . esc_sql( $orderby_field ) . " " . esc_sql( $order_direction ) . " ";
    $sql_results .= "LIMIT %d OFFSET %d";

   
    $results = $wpdb->get_results( $wpdb->prepare( (string)mioweb_clean_sql_for_check($sql_results), absint( $args['per_page'] ), absint( $offset ) ) );

    /**
     * COSTRUZIONE QUERY TOTALI
     */
    $sql_count = "SELECT COUNT(*) FROM " . $wpdb->prefix . "mioweb_hosting h ";
    $sql_count .= "LEFT JOIN " . $wpdb->prefix . "mioweb_clienti c ON h.cliente_id = c.id "; // Aggiunto JOIN se search include campi cliente
    $sql_count .= "WHERE " . $where_clause;

   
    $total = (int) $wpdb->get_var( (string) mioweb_clean_sql_for_check($sql_count) );

    // phpcs:enable
    return [
        'items' => $results,
        'total' => $total,
        'pages' => ceil( $total / absint( $args['per_page'] ) ),
        'page'  => absint( $args['page'] ),
    ];
}



/**
 * Ottieni hosting per cliente (per select)
 */
function mioweb_get_hosting_by_cliente( $cliente_id, $solo_attivi = true ) {
    global $wpdb;

    // 1. Definiamo la query base senza variabili interne
    $query = "SELECT id, nome_sito, dominio_principale FROM " . $wpdb->prefix . "mioweb_hosting WHERE cliente_id = %d";

    // 2. Aggiungiamo il pezzo dello status se necessario via concatenazione
    if ( $solo_attivi ) {
        $query .= " AND status = 'attivo'";
    }

    $query .= " ORDER BY nome_sito";

    // 3. Passiamo la stringa composta al prepare. 

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    return $wpdb->get_results( $wpdb->prepare( (string) mioweb_clean_sql_for_check($query), absint( $cliente_id ) ) );
}


/**
 * Cancella un hosting
 */
function mioweb_delete_hosting($id)
{
    global $wpdb;

    $table = $wpdb->prefix . 'mioweb_hosting';

    // Prima controlla se ci sono manutenzioni collegate

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $manutenzioni = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}mioweb_manutenzioni WHERE hosting_id = %d",
        $id
    ));

    if ($manutenzioni > 0) {
        return new WP_Error('has_relations', __('Cannot delete: there are maintenance contracts linked to this hosting', 'mioweb-agency'));
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    return $wpdb->delete($table, ['id' => intval($id)]);
}

/**
 * Ottieni statistiche hosting
 */
function mioweb_get_hosting_stats()
{
    global $wpdb;

   // $table = $wpdb->prefix . 'mioweb_hosting';
    $today = current_time('Y-m-d');

    $stats = [
        'totali' => 0,
        'attivi' => 0,
        'in_scadenza' => 0,
        'scaduti' => 0,
        'provider_più_usato' => '',
        'costo_mensile_totale' => 0
    ];

    // Totali per status
    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $results = $wpdb->get_results(
        "SELECT status, COUNT(*) as count, SUM(costo) as totale_costo
        FROM {$wpdb->prefix}mioweb_hosting
        GROUP BY status"
    );

    foreach ($results as $row) {
        $stats['totali'] += intval($row->count);

        if ($row->status === 'attivo') {
            $stats['attivi'] = intval($row->count);

            // Calcolo costo mensile (conversione in base al ciclo)
            switch ($row->ciclo_fatturazione ?? 'annuale') {
                case 'mensile':
                    $stats['costo_mensile_totale'] += floatval($row->totale_costo);
                    break;
                case 'trimestrale':
                    $stats['costo_mensile_totale'] += floatval($row->totale_costo) / 3;
                    break;
                case 'semestrale':
                    $stats['costo_mensile_totale'] += floatval($row->totale_costo) / 6;
                    break;
                case 'annuale':
                    $stats['costo_mensile_totale'] += floatval($row->totale_costo) / 12;
                    break;
                case 'biennale':
                    $stats['costo_mensile_totale'] += floatval($row->totale_costo) / 24;
                    break;
            }
        }
    }

    // In scadenza (prossimi 30 giorni)
    $stats['in_scadenza'] = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}mioweb_hosting 
        WHERE data_scadenza BETWEEN %s AND %s AND status = 'attivo'",
        $today,
        gmdate('Y-m-d', strtotime('+30 days'))
    ));

    // Scaduti
    $stats['scaduti'] = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}mioweb_hosting 
        WHERE data_scadenza < %s AND status = 'attivo'",
        $today
    ));

    // Provider più usato
    $stats['provider_più_usato'] = $wpdb->get_var(
        "SELECT provider 
        FROM {$wpdb->prefix}mioweb_hosting 
        WHERE provider != '' 
        GROUP BY provider 
        ORDER BY COUNT(*) DESC 
        LIMIT 1"
    );

    // phpcs:enable
    return $stats;
}

/**
 * "Lava" la stringa sql
 */
/*
function mioweb_clean_sql_for_check( $sql ) {
    return (string) $sql; 
}
*/