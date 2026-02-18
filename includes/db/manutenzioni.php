<?php

/**
 * CRUD Manutenzioni
 * Funzioni per gestire la tabella mioweb_manutenzioni
 *
 * @package MioWebAgency
 */

// Evita accesso diretto
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Crea una nuova manutenzione
 */
function mioweb_create_manutenzione($data)
{
    global $wpdb;

    $table = $wpdb->prefix . 'mioweb_manutenzioni';

    $defaults = [
        'cliente_id' => 0,
        'hosting_id' => null,
        'sito_id' => null,
        'tipo' => 'base',
        'nome_contratto' => '',
        'data_inizio' => current_time('Y-m-d'),
        'data_fine' => null,
        'prossimo_rinnovo' => null,
        'importo' => 0,
        'valuta' => 'EUR',
        'ciclodi_rinnovo' => 'annuale',
        'stato' => 'attivo',
        'note' => ''
    ];

    $data = wp_parse_args($data, $defaults);

    // Calcola prossimo rinnovo se non fornito
    if (empty($data['prossimo_rinnovo']) && ! empty($data['data_inizio'])) {
        $data['prossimo_rinnovo'] = mioweb_calcola_prossimo_rinnovo(
            $data['data_inizio'],
            $data['ciclodi_rinnovo']
        );
    }

    // Sanitizzazione
    $manutenzione = [
        'cliente_id' => intval($data['cliente_id']),
        'hosting_id' => ! empty($data['hosting_id']) ? intval($data['hosting_id']) : null,
        'sito_id' => ! empty($data['sito_id']) ? intval($data['sito_id']) : null,
        'tipo' => sanitize_text_field($data['tipo']),
        'nome_contratto' => sanitize_text_field($data['nome_contratto']),
        'data_inizio' => sanitize_text_field($data['data_inizio']),
        'data_fine' => ! empty($data['data_fine']) ? sanitize_text_field($data['data_fine']) : null,
        'prossimo_rinnovo' => ! empty($data['prossimo_rinnovo']) ? sanitize_text_field($data['prossimo_rinnovo']) : null,
        'importo' => floatval($data['importo']),
        'valuta' => sanitize_text_field($data['valuta']),
        'ciclodi_rinnovo' => sanitize_text_field($data['ciclodi_rinnovo']),
        'stato' => sanitize_text_field($data['stato']),
        'note' => sanitize_textarea_field($data['note'])
    ];

    // Validazione
    if (empty($manutenzione['cliente_id'])) {
        return new WP_Error('no_cliente', __('Client is required', 'mioweb-agency'));
    }

    if (empty($manutenzione['data_inizio'])) {
        return new WP_Error('no_data_inizio', __('Start date is required', 'mioweb-agency'));
    }
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
    $wpdb->insert($table, $manutenzione);

    if ($wpdb->insert_id) {
        return $wpdb->insert_id;
    }

    return new WP_Error('insert_failed', __('Failed to create maintenance contract', 'mioweb-agency'));
}

/**
 * Aggiorna una manutenzione esistente
 */
function mioweb_update_manutenzione($id, $data)
{
    global $wpdb;

    $table = $wpdb->prefix . 'mioweb_manutenzioni';

    $manutenzione = [];

    // Campi aggiornabili
    $allowed_fields = [
        'cliente_id',
        'hosting_id',
        'sito_id',
        'tipo',
        'nome_contratto',
        'data_inizio',
        'data_fine',
        'prossimo_rinnovo',
        'importo',
        'valuta',
        'ciclodi_rinnovo',
        'stato',
        'note'
    ];

    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            if ($field === 'note') {
                $manutenzione[$field] = sanitize_textarea_field($data[$field]);
            } elseif ($field === 'importo') {
                $manutenzione[$field] = floatval($data[$field]);
            } elseif (in_array($field, ['cliente_id', 'hosting_id', 'sito_id'])) {
                $manutenzione[$field] = ! empty($data[$field]) ? intval($data[$field]) : null;
            } else {
                $manutenzione[$field] = sanitize_text_field($data[$field]);
            }
        }
    }

    // Ricalcola stato se necessario
    if (isset($manutenzione['prossimo_rinnovo']) || isset($manutenzione['stato'])) {
        $manutenzione['stato'] = mioweb_calcola_stato_manutenzione(
            $manutenzione['prossimo_rinnovo'] ?? null,
            $manutenzione['stato'] ?? null
        );
    }

    if (empty($manutenzione)) {
        return false;
    }
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    return $wpdb->update($table, $manutenzione, ['id' => intval($id)]);
}

/**
 * Ottieni una manutenzione per ID
 */
function mioweb_get_manutenzione($id)
{
    global $wpdb;

    //$table = $wpdb->prefix . 'mioweb_manutenzioni';

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mioweb_manutenzioni WHERE id = %d",
        intval($id)
    ));
}

/**
 * Ottieni tutte le manutenzioni con paginazione e filtri
 */
function mioweb_get_manutenzioni($args = [])
{
    global $wpdb;

    $table = $wpdb->prefix . 'mioweb_manutenzioni';
    $table_clienti = $wpdb->prefix . 'mioweb_clienti';

    $defaults = [
        'per_page' => 20,
        'page' => 1,
        'orderby' => 'prossimo_rinnovo',
        'order' => 'ASC',
        'search' => '',
        'cliente_id' => 0,
        'stato' => '',
        'tipo' => '',
        'scadenza' => '' // 'imminente', 'scaduto', 'future'
    ];

    $args = wp_parse_args($args, $defaults);

    $where = ['1=1'];

    if (! empty($args['search'])) {
        $search = '%' . $wpdb->esc_like($args['search']) . '%';
        $where[] = $wpdb->prepare(
            "(m.nome_contratto LIKE %s OR c.ragione_sociale LIKE %s OR c.nome LIKE %s OR c.cognome LIKE %s)",
            $search,
            $search,
            $search,
            $search
        );
    }

    if (! empty($args['cliente_id'])) {
        $where[] = $wpdb->prepare("m.cliente_id = %d", $args['cliente_id']);
    }

    if (! empty($args['stato'])) {
        $where[] = $wpdb->prepare("m.stato = %s", $args['stato']);
    }

    if (! empty($args['tipo'])) {
        $where[] = $wpdb->prepare("m.tipo = %s", $args['tipo']);
    }

    // Filtri per scadenza
    $today = current_time('Y-m-d');
    if ($args['scadenza'] === 'imminente') {
        $where[] = $wpdb->prepare(
            "m.prossimo_rinnovo BETWEEN %s AND %s",
            $today,
            gmdate('Y-m-d', strtotime('+30 days'))
        );
    } elseif ($args['scadenza'] === 'scaduto') {
        $where[] = $wpdb->prepare("m.prossimo_rinnovo < %s", $today);
    } elseif ($args['scadenza'] === 'future') {
        $where[] = $wpdb->prepare("m.prossimo_rinnovo >= %s", $today);
    }

    $where_clause = implode(' AND ', $where);

    $offset = ($args['page'] - 1) * $args['per_page'];

    $orderby = in_array($args['orderby'], ['id', 'cliente_id', 'tipo', 'prossimo_rinnovo', 'importo', 'stato'])
        ? "m.{$args['orderby']}"
        : 'm.prossimo_rinnovo';

    $order = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';

    // Query con JOIN per ottenere dati cliente 

    // 1. Costruiamo la base della query SENZA variabili interpolate
    $sql = "SELECT m.*, c.ragione_sociale, c.nome, c.cognome, c.email ";
    $sql .= "FROM " . $wpdb->prefix . "mioweb_manutenzioni m ";
    $sql .= "LEFT JOIN " . $wpdb->prefix . "mioweb_clienti c ON m.cliente_id = c.id ";
    $sql .= "WHERE " . $where_clause . " "; // Concatenazione, non interpolazione
    $sql .= "ORDER BY " . esc_sql($orderby) . " " . esc_sql($order) . " ";
    $sql .= "LIMIT %d OFFSET %d";

    // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $results = $wpdb->get_results(
        $wpdb->prepare(
            (string)mioweb_clean_sql_for_check($sql),
            absint($args['per_page']),
            absint($offset)
        )
    );

    $sql_count = "SELECT COUNT(*) FROM " . $wpdb->prefix . "mioweb_manutenzioni m WHERE " . $where_clause;
    $total = $wpdb->get_var( $sql_count );
   
    // Aggiorna stati in base alle date
    foreach ($results as $manutenzione) {
        $manutenzione->stato_calcolato = mioweb_calcola_stato_manutenzione(
            $manutenzione->prossimo_rinnovo,
            $manutenzione->stato
        );
    }

    // phpcs:enable
    return [
        'items' => $results,
        'total' => intval($total),
        'pages' => ceil($total / $args['per_page']),
        'page' => $args['page']
    ];
}

/**
 * Calcola il prossimo rinnovo in base alla data inizio e ciclo
 */
function mioweb_calcola_prossimo_rinnovo($data_inizio, $ciclo)
{
    if (empty($data_inizio)) {
        return null;
    }

    $date = new DateTime($data_inizio);
    $today = new DateTime(current_time('Y-m-d'));

    // Se la data inizio è nel futuro, usa quella
    if ($date > $today) {
        return $data_inizio;
    }

    // Altrimenti calcola la prossima ricorrenza
    while ($date <= $today) {
        switch ($ciclo) {
            case 'mensile':
                $date->modify('+1 month');
                break;
            case 'trimestrale':
                $date->modify('+3 months');
                break;
            case 'semestrale':
                $date->modify('+6 months');
                break;
            case 'annuale':
            default:
                $date->modify('+1 year');
                break;
        }
    }

    return $date->format('Y-m-d');
}

/**
 * Calcola lo stato in base alla data di rinnovo
 */
function mioweb_calcola_stato_manutenzione($prossimo_rinnovo, $stato_attuale = null)
{
    // Se già cancellato o sospeso, mantieni
    if (in_array($stato_attuale, ['sospeso', 'cancellato'])) {
        return $stato_attuale;
    }

    if (empty($prossimo_rinnovo)) {
        return $stato_attuale ?: 'attivo';
    }

    $today = new DateTime(current_time('Y-m-d'));
    $rinnovo = new DateTime($prossimo_rinnovo);
    $diff = $today->diff($rinnovo)->days;

    if ($rinnovo < $today) {
        return 'scaduto';
    } elseif ($diff <= 30) {
        return 'in_scadenza';
    } else {
        return 'attivo';
    }
}

/**
 * Cancella una manutenzione
 */
function mioweb_delete_manutenzione($id)
{
    global $wpdb;

    $table = $wpdb->prefix . 'mioweb_manutenzioni';

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    return $wpdb->delete($table, ['id' => intval($id)]);
}

/**
 * Ottieni statistiche manutenzioni
 */
function mioweb_get_manutenzioni_stats()
{
    global $wpdb;

    $table = $wpdb->prefix . 'mioweb_manutenzioni';
    $today = current_time('Y-m-d');

    $stats = [
        'totali' => 0,
        'attive' => 0,
        'in_scadenza' => 0,
        'scadute' => 0,
        'importo_mensile' => 0,
        'importo_annuale' => 0
    ];

    // Totali per stato

    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $results = $wpdb->get_results(
        "SELECT stato, COUNT(*) as count 
        FROM {$wpdb->prefix}mioweb_manutenzioni
        GROUP BY stato"
    );

    foreach ($results as $row) {
        $stats[$row->stato] = intval($row->count);
        $stats['totali'] += intval($row->count);
    }

    // Calcolo importi (solo attive)
    $attive = $wpdb->get_results(
        "SELECT importo, valuta, ciclodi_rinnovo 
        FROM {$wpdb->prefix}mioweb_manutenzioni 
        WHERE stato IN ('attivo', 'in_scadenza')"
    );

    foreach ($attive as $m) {
        $importo = floatval($m->importo);

        // Conversione approssimativa in EUR mensile
        switch ($m->ciclodi_rinnovo) {
            case 'mensile':
                $stats['importo_mensile'] += $importo;
                $stats['importo_annuale'] += $importo * 12;
                break;
            case 'trimestrale':
                $stats['importo_mensile'] += $importo / 3;
                $stats['importo_annuale'] += $importo * 4;
                break;
            case 'semestrale':
                $stats['importo_mensile'] += $importo / 6;
                $stats['importo_annuale'] += $importo * 2;
                break;
            case 'annuale':
                $stats['importo_mensile'] += $importo / 12;
                $stats['importo_annuale'] += $importo;
                break;
        }
    }

    // Manutenzioni in scadenza nei prossimi 30 giorni
    $stats['in_scadenza'] = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}mioweb_manutenzioni 
        WHERE prossimo_rinnovo BETWEEN %s AND %s",
        $today,
        gmdate('Y-m-d', strtotime('+30 days'))
    ));

    // phpcs:enable
    return $stats;
}
