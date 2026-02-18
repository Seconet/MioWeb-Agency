<?php

/**
 * Admin Page: Lista Manutenzioni
 *
 * @package MioWebAgency
 */

// Evita accesso diretto
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Renderizza la pagina lista manutenzioni
 */
function mioweb_render_manutenzioni_list()
{

    // Enqueue style direttamente qui
    wp_enqueue_style(
        'mioweb-manutenzioni-list',
        MIOWEB_PLUGIN_URL . 'includes/css/manutenzioni-list.css',
        [],
        MIOWEB_VERSION
    );

    // Messaggi di feedback
    if (isset($_GET['success']) && isset($_GET['action'])) {
        if ($_GET['action'] === 'created') {
            echo '<div class="notice notice-success is-dismissible"><p>' .
                esc_html__('Maintenance contract created successfully.', 'mioweb-agency') .
                '</p></div>';
        }
        if ($_GET['action'] === 'updated') {
            echo '<div class="notice notice-success is-dismissible"><p>' .
                esc_html__('Maintenance contract updated successfully.', 'mioweb-agency') .
                '</p></div>';
        }
        if ($_GET['action'] === 'deleted') {
            echo '<div class="notice notice-success is-dismissible"><p>' .
                esc_html__('Maintenance contract deleted successfully.', 'mioweb-agency') .
                '</p></div>';
        }
    }

    // Gestione azione delete
    // Gestione azione delete
    $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
    $id     = isset($_GET['id']) ? intval(wp_unslash($_GET['id'])) : 0;
    $nonce  = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

    if ($action === 'delete' && $id > 0 && wp_verify_nonce($nonce, 'delete_manutenzione_' . $id)) {

        mioweb_delete_manutenzione($id);
        wp_safe_redirect(admin_url('admin.php?page=mioweb-manutenzioni&success=1&action=deleted'));
        exit;
    }


    // Parametri di filtro
    $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
    $cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;
    $stato = isset($_GET['stato']) ? sanitize_text_field(wp_unslash($_GET['stato'])) : '';
    $tipo = isset($_GET['tipo']) ? sanitize_text_field(wp_unslash($_GET['tipo'])) : '';
    $scadenza = isset($_GET['scadenza']) ? sanitize_text_field(wp_unslash($_GET['scadenza'])) : '';
    $page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

    $manutenzioni = mioweb_get_manutenzioni([
        'search' => $search,
        'cliente_id' => $cliente_id,
        'stato' => $stato,
        'tipo' => $tipo,
        'scadenza' => $scadenza,
        'page' => $page,
        'per_page' => 20
    ]);

    // Ottieni lista clienti per il filtro
    global $wpdb;
    // Prova a prendere dal cache
    $clienti = wp_cache_get('mioweb_clienti_list');

    if (false === $clienti) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $clienti = $wpdb->get_results(
            "SELECT id, ragione_sociale, nome, cognome 
        FROM {$wpdb->prefix}mioweb_clienti 
        ORDER BY ragione_sociale, nome"
        );
        wp_cache_set('mioweb_clienti_list', $clienti, '', HOUR_IN_SECONDS);
    }
?>

    <div class="wrap mioweb-manutenzioni-wrap">
        <h1 class="wp-heading-inline">
            <?php esc_html_e('Maintenance Contracts', 'mioweb-agency'); ?>
        </h1>

        <a href="?page=mioweb-manutenzioni-form" class="page-title-action">
            <?php esc_html_e('Add New Contract', 'mioweb-agency'); ?>
        </a>

        <hr class="wp-header-end">

        <!-- Statistiche rapide -->
        <?php $stats = mioweb_get_manutenzioni_stats(); ?>
        <div class="mioweb-stats-cards">
            <div class="mioweb-stat-card">
                <span class="mioweb-stat-label"><?php esc_html_e('Total', 'mioweb-agency'); ?></span>
                <span class="mioweb-stat-value"><?php echo esc_html($stats['totali']); ?></span>
            </div>
            <div class="mioweb-stat-card">
                <span class="mioweb-stat-label"><?php esc_html_e('Active', 'mioweb-agency'); ?></span>
                <span class="mioweb-stat-value"><?php echo esc_html($stats['attive']); ?></span>
            </div>
            <div class="mioweb-stat-card warning">
                <span class="mioweb-stat-label"><?php esc_html_e('Expiring', 'mioweb-agency'); ?></span>
                <span class="mioweb-stat-value"><?php echo esc_html($stats['in_scadenza']); ?></span>
            </div>
            <div class="mioweb-stat-card expired">
                <span class="mioweb-stat-label"><?php esc_html_e('Expired', 'mioweb-agency'); ?></span>
                <span class="mioweb-stat-value"><?php echo esc_html($stats['scadute']); ?></span>
            </div>
            <div class="mioweb-stat-card">
                <span class="mioweb-stat-label"><?php esc_html_e('Monthly Revenue', 'mioweb-agency'); ?></span>
                <span class="mioweb-stat-value">€ <?php echo number_format($stats['importo_mensile'], 2); ?></span>
            </div>
        </div>

        <!-- Filtri -->
        <div class="mioweb-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="mioweb-manutenzioni">

                <div class="mioweb-filter-row">
                    <select name="cliente_id">
                        <option value=""><?php esc_html_e('All clients', 'mioweb-agency'); ?></option>
                        <?php foreach ($clienti as $cliente) : ?>
                            <option value="<?php echo esc_html($cliente->id); ?>" <?php selected($cliente_id, $cliente->id); ?>>
                                <?php
                                if ($cliente->ragione_sociale) {
                                    echo esc_html($cliente->ragione_sociale);
                                } else {
                                    echo esc_html(trim($cliente->nome . ' ' . $cliente->cognome));
                                }
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="stato">
                        <option value=""><?php esc_html_e('All status', 'mioweb-agency'); ?></option>
                        <option value="attivo" <?php selected($stato, 'attivo'); ?>>
                            <?php esc_html_e('Active', 'mioweb-agency'); ?>
                        </option>
                        <option value="in_scadenza" <?php selected($stato, 'in_scadenza'); ?>>
                            <?php esc_html_e('Expiring soon', 'mioweb-agency'); ?>
                        </option>
                        <option value="scaduto" <?php selected($stato, 'scaduto'); ?>>
                            <?php esc_html_e('Expired', 'mioweb-agency'); ?>
                        </option>
                        <option value="sospeso" <?php selected($stato, 'sospeso'); ?>>
                            <?php esc_html_e('Suspended', 'mioweb-agency'); ?>
                        </option>
                    </select>

                    <select name="tipo">
                        <option value=""><?php esc_html_e('All types', 'mioweb-agency'); ?></option>
                        <option value="base" <?php selected($tipo, 'base'); ?>>
                            <?php esc_html_e('Base', 'mioweb-agency'); ?>
                        </option>
                        <option value="professional" <?php selected($tipo, 'professional'); ?>>
                            <?php esc_html_e('Professional', 'mioweb-agency'); ?>
                        </option>
                        <option value="premium" <?php selected($tipo, 'premium'); ?>>
                            <?php esc_html_e('Premium', 'mioweb-agency'); ?>
                        </option>
                        <option value="custom" <?php selected($tipo, 'custom'); ?>>
                            <?php esc_html_e('Custom', 'mioweb-agency'); ?>
                        </option>
                    </select>

                    <select name="scadenza">
                        <option value=""><?php esc_html_e('All deadlines', 'mioweb-agency'); ?></option>
                        <option value="imminente" <?php selected($scadenza, 'imminente'); ?>>
                            <?php esc_html_e('Next 30 days', 'mioweb-agency'); ?>
                        </option>
                        <option value="scaduto" <?php selected($scadenza, 'scaduto'); ?>>
                            <?php esc_html_e('Expired', 'mioweb-agency'); ?>
                        </option>
                        <option value="future" <?php selected($scadenza, 'future'); ?>>
                            <?php esc_html_e('Future', 'mioweb-agency'); ?>
                        </option>
                    </select>

                    <input type="text"
                        name="s"
                        placeholder="<?php esc_attr_e('Search contracts...', 'mioweb-agency'); ?>"
                        value="<?php echo esc_attr($search); ?>">

                    <button type="submit" class="button">
                        <?php esc_html_e('Filter', 'mioweb-agency'); ?>
                    </button>

                    <a href="?page=mioweb-manutenzioni" class="button">
                        <?php esc_html_e('Reset', 'mioweb-agency'); ?>
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabella manutenzioni -->
        <table class="wp-list-table widefat fixed striped mioweb-table">
            <thead>
                <tr>
                    <th scope="col" width="50">ID</th>
                    <th scope="col"><?php esc_html_e('Client', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Contract', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Type', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Next Renewal', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Amount', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Status', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Actions', 'mioweb-agency'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($manutenzioni['items'])) : ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">
                            <?php esc_html_e('No maintenance contracts found.', 'mioweb-agency'); ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($manutenzioni['items'] as $m) :
                        $stato_class = '';
                        $stato_label = '';

                        switch ($m->stato_calcolato) {
                            case 'attivo':
                                $stato_class = 'status-active';
                                $stato_label = __('Active', 'mioweb-agency');
                                break;
                            case 'in_scadenza':
                                $stato_class = 'status-warning';
                                $stato_label = __('Expiring soon', 'mioweb-agency');
                                break;
                            case 'scaduto':
                                $stato_class = 'status-expired';
                                $stato_label = __('Expired', 'mioweb-agency');
                                break;
                            case 'sospeso':
                                $stato_class = 'status-suspended';
                                $stato_label = __('Suspended', 'mioweb-agency');
                                break;
                            default:
                                $stato_class = 'status-active';
                                $stato_label = ucfirst($m->stato);
                        }

                        // Nome cliente
                        $nome_cliente = '';
                        if ($m->ragione_sociale) {
                            $nome_cliente = $m->ragione_sociale;
                        } elseif ($m->nome || $m->cognome) {
                            $nome_cliente = trim($m->nome . ' ' . $m->cognome);
                        } else {
                            $nome_cliente = '#' . $m->cliente_id;
                        }
                    ?>
                        <tr>
                            <td><?php echo esc_html($m->id); ?></td>
                            <td>
                                <strong>
                                    <a href="?page=mioweb-cliente-form&id=<?php echo esc_html($m->cliente_id); ?>">
                                        <?php echo esc_html($nome_cliente); ?>
                                    </a>
                                </strong>
                                <?php if ($m->email) : ?>
                                    <br><small><?php echo esc_html($m->email); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong>
                                    <a href="?page=mioweb-manutenzioni-form&id=<?php echo esc_html($m->id); ?>">
                                        <?php echo esc_html($m->nome_contratto ?: '#' . $m->id); ?>
                                    </a>
                                </strong>
                            </td>
                            <td>
                                <?php
                                $tipi = [
                                    'base' => __('Base', 'mioweb-agency'),
                                    'professional' => __('Professional', 'mioweb-agency'),
                                    'premium' => __('Premium', 'mioweb-agency'),
                                    'custom' => __('Custom', 'mioweb-agency')
                                ];
                                echo esc_html($tipi[$m->tipo] ?? $m->tipo);
                                ?>
                            </td>
                            <td>
                                <?php if ($m->prossimo_rinnovo) : ?>
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($m->prossimo_rinnovo))); ?>
                                    <br><small><?php echo esc_html($m->ciclodi_rinnovo); ?></small>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($m->importo > 0) : ?>
                                    <?php echo esc_html($m->valuta); ?> <?php echo number_format($m->importo, 2); ?>
                                    <br><small><?php echo esc_html($m->ciclodi_rinnovo); ?></small>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="mioweb-status <?php echo esc_html($stato_class); ?>">
                                    <?php echo esc_html($stato_label); ?>
                                </span>
                            </td>
                            <td>
                                <div class="mioweb-actions">
                                    <a href="?page=mioweb-manutenzioni-form&id=<?php echo esc_html($m->id); ?>"
                                        class="button button-small">
                                        <?php esc_html_e('Edit', 'mioweb-agency'); ?>
                                    </a>

                                    <a href="<?php echo esc_url(wp_nonce_url(
                                                    admin_url('admin.php?page=mioweb-manutenzioni&action=delete&id=' . $m->id),
                                                    'delete_manutenzione_' . $m->id
                                                )); ?>"
                                        class="button button-small mioweb-delete"
                                        onclick="return confirm('<?php esc_attr_e('Delete this maintenance contract?', 'mioweb-agency'); ?>')">
                                        <?php esc_html_e('Delete', 'mioweb-agency'); ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginazione -->
        <?php if ($manutenzioni['pages'] > 1) : ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    $paginate_args = [
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $manutenzioni['pages'],
                        'current' => $manutenzioni['page']
                    ];
                    echo wp_kses_post(paginate_links($paginate_args));
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>


<?php
}
