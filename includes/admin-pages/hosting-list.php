<?php

/**
 * Admin Page: Lista Hosting
 *
 * @package MioWebAgency
 */

// Evita accesso diretto
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Renderizza la pagina lista hosting
 */
function mioweb_render_hosting_list()
{

    // Enqueue style direttamente qui
    wp_enqueue_style(
        'mioweb-hosting-list',
        MIOWEB_PLUGIN_URL . 'includes/css/hosting-list.css',
        [],
        MIOWEB_VERSION
    );

    // Messaggi di feedback
    if (isset($_GET['success']) && isset($_GET['action'])) {
        if ($_GET['action'] === 'created') {
            echo '<div class="notice notice-success is-dismissible"><p>' .
                esc_html__('Hosting created successfully.', 'mioweb-agency') .
                '</p></div>';
        }
        if ($_GET['action'] === 'updated') {
            echo '<div class="notice notice-success is-dismissible"><p>' .
                esc_html__('Hosting updated successfully.', 'mioweb-agency') .
                '</p></div>';
        }
        if ($_GET['action'] === 'deleted') {
            echo '<div class="notice notice-success is-dismissible"><p>' .
                esc_html__('Hosting deleted successfully.', 'mioweb-agency') .
                '</p></div>';
        }
    }

    // Gestione azione delete
    $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
    $id     = isset($_GET['id']) ? intval(wp_unslash($_GET['id'])) : 0;
    $nonce  = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

    if ($action === 'delete' && $id > 0 && wp_verify_nonce($nonce, 'delete_hosting_' . $id)) {

        $result = mioweb_delete_hosting($id);

        if (is_wp_error($result)) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result->get_error_message()) . '</p></div>';
        } else {
            wp_safe_redirect(admin_url('admin.php?page=mioweb-hosting&success=1&action=deleted'));
            exit;
        }
    }


    // Parametri di filtro
    $search = isset($_GET['s']) ? sanitize_text_field( wp_unslash($_GET['s']) ) : '';
    $cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;
    $status = isset($_GET['status']) ? sanitize_text_field( wp_unslash( $_GET['status']) ) : '';
    $provider = isset($_GET['provider']) ? sanitize_text_field( wp_unslash($_GET['provider']) ) : '';
    $scadenza = isset($_GET['scadenza']) ? sanitize_text_field( wp_unslash($_GET['scadenza']) ) : '';
    $page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

    $hosting_list = mioweb_get_hosting_list([
        'search' => $search,
        'cliente_id' => $cliente_id,
        'status' => $status,
        'provider' => $provider,
        'scadenza' => $scadenza,
        'page' => $page,
        'per_page' => 20
    ]);

    // Ottieni lista clienti per il filtro
    global $wpdb;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $clienti = $wpdb->get_results( 
        "SELECT id, ragione_sociale, nome, cognome 
        FROM {$wpdb->prefix}mioweb_clienti 
        ORDER BY ragione_sociale, nome"
    );

    // Ottieni provider unici per filtro

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $providers = $wpdb->get_col( 
        "SELECT DISTINCT provider 
        FROM {$wpdb->prefix}mioweb_hosting 
        WHERE provider != '' 
        ORDER BY provider"
    );

    // Statistiche
    $stats = mioweb_get_hosting_stats();
?>

    <div class="wrap mioweb-hosting-wrap">
        <h1 class="wp-heading-inline">
            <?php esc_html_e('Hosting Plans', 'mioweb-agency'); ?>
        </h1>

        <a href="?page=mioweb-hosting-form" class="page-title-action">
            <?php esc_html_e('Add New Hosting', 'mioweb-agency'); ?>
        </a>

        <hr class="wp-header-end">

        <!-- Statistiche rapide -->
        <div class="mioweb-stats-cards">
            <div class="mioweb-stat-card">
                <span class="mioweb-stat-label"><?php esc_html_e('Total', 'mioweb-agency'); ?></span>
                <span class="mioweb-stat-value"><?php echo esc_html($stats['totali']); ?></span>
            </div>
            <div class="mioweb-stat-card">
                <span class="mioweb-stat-label"><?php esc_html_e('Active', 'mioweb-agency'); ?></span>
                <span class="mioweb-stat-value"><?php echo esc_html($stats['attivi']); ?></span>
            </div>
            <div class="mioweb-stat-card warning">
                <span class="mioweb-stat-label"><?php esc_html_e('Expiring', 'mioweb-agency'); ?></span>
                <span class="mioweb-stat-value"><?php echo esc_html($stats['in_scadenza']); ?></span>
            </div>
            <div class="mioweb-stat-card expired">
                <span class="mioweb-stat-label"><?php esc_html_e('Expired', 'mioweb-agency'); ?></span>
                <span class="mioweb-stat-value"><?php echo esc_html($stats['scaduti']); ?></span>
            </div>
            <div class="mioweb-stat-card">
                <span class="mioweb-stat-label"><?php esc_html_e('Monthly Cost', 'mioweb-agency'); ?></span>
                <span class="mioweb-stat-value">€ <?php echo number_format($stats['costo_mensile_totale'], 2); ?></span>
            </div>
            <div class="mioweb-stat-card">
                <span class="mioweb-stat-label"><?php esc_html_e('Top Provider', 'mioweb-agency'); ?></span>
                <span class="mioweb-stat-value"><?php echo esc_html($stats['provider_più_usato'] ?: '—'); ?></span>
            </div>
        </div>

        <!-- Filtri -->
        <div class="mioweb-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="mioweb-hosting">

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

                    <select name="status">
                        <option value=""><?php esc_html_e('All status', 'mioweb-agency'); ?></option>
                        <option value="attivo" <?php selected($status, 'attivo'); ?>>
                            <?php esc_html_e('Active', 'mioweb-agency'); ?>
                        </option>
                        <option value="sospeso" <?php selected($status, 'sospeso'); ?>>
                            <?php esc_html_e('Suspended', 'mioweb-agency'); ?>
                        </option>
                        <option value="cancellato" <?php selected($status, 'cancellato'); ?>>
                            <?php esc_html_e('Cancelled', 'mioweb-agency'); ?>
                        </option>
                    </select>

                    <select name="provider">
                        <option value=""><?php esc_html_e('All providers', 'mioweb-agency'); ?></option>
                        <?php foreach ($providers as $p) : ?>
                            <option value="<?php echo esc_attr($p); ?>" <?php selected($provider, $p); ?>>
                                <?php echo esc_html($p); ?>
                            </option>
                        <?php endforeach; ?>
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
                        placeholder="<?php esc_attr_e('Search hosting...', 'mioweb-agency'); ?>"
                        value="<?php echo esc_attr($search); ?>">

                    <button type="submit" class="button">
                        <?php esc_html_e('Filter', 'mioweb-agency'); ?>
                    </button>

                    <a href="?page=mioweb-hosting" class="button">
                        <?php esc_html_e('Reset', 'mioweb-agency'); ?>
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabella hosting -->
        <table class="wp-list-table widefat fixed striped mioweb-table">
            <thead>
                <tr>
                    <th scope="col" width="50">ID</th>
                    <th scope="col"><?php esc_html_e('Client', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Site Name', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Domain', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Provider', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Expiry Date', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Cost', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Status', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Actions', 'mioweb-agency'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($hosting_list['items'])) : ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">
                            <?php esc_html_e('No hosting plans found.', 'mioweb-agency'); ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($hosting_list['items'] as $h) :
                        // Calcola stato scadenza per colore
                        $today = current_time('Y-m-d');
                        $expiry_class = '';
                        if ($h->data_scadenza) {
                            if ($h->data_scadenza < $today) {
                                $expiry_class = 'expired';
                            } elseif ($h->data_scadenza <= gmdate('Y-m-d', strtotime('+30 days'))) {
                                $expiry_class = 'warning';
                            }
                        }

                        // Nome cliente
                        $nome_cliente = '';
                        if ($h->ragione_sociale) {
                            $nome_cliente = $h->ragione_sociale;
                        } elseif ($h->nome || $h->cognome) {
                            $nome_cliente = trim($h->nome . ' ' . $h->cognome);
                        } else {
                            $nome_cliente = '#' . $h->cliente_id;
                        }
                    ?>
                        <tr>
                            <td><?php echo esc_html($h->id); ?></td>
                            <td>
                                <strong>
                                    <a href="?page=mioweb-cliente-form&id=<?php echo esc_html($h->cliente_id); ?>">
                                        <?php echo esc_html($nome_cliente); ?>
                                    </a>
                                </strong>
                                <?php if ($h->email) : ?>
                                    <br><small><?php echo esc_html($h->email); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong>
                                    <a href="?page=mioweb-hosting-form&id=<?php echo esc_html($h->id); ?>">
                                        <?php echo esc_html($h->nome_sito); ?>
                                    </a>
                                </strong>
                            </td>
                            <td>
                                <?php if ($h->dominio_principale) : ?>
                                    <a href="http://<?php echo esc_attr($h->dominio_principale); ?>" target="_blank">
                                        <?php echo esc_html($h->dominio_principale); ?>
                                    </a>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($h->provider ?: '—'); ?></td>
                            <td class="<?php echo esc_attr($expiry_class); ?>">
                                <?php if (! empty($h->data_scadenza)) : ?>
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($h->data_scadenza))); ?>
                                <?php else : ?>
                                    <?php esc_html_e('—', 'mioweb-agency'); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($h->costo > 0) : ?>
                                    <?php echo esc_html($h->valuta); ?> <?php echo number_format($h->costo, 2); ?>
                                    <br><small><?php echo esc_html($h->ciclo_fatturazione); ?></small>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status_class = '';
                                $status_label = '';

                                switch ($h->status) {
                                    case 'attivo':
                                        $status_class = 'status-active';
                                        $status_label = __('Active', 'mioweb-agency');
                                        break;
                                    case 'sospeso':
                                        $status_class = 'status-suspended';
                                        $status_label = __('Suspended', 'mioweb-agency');
                                        break;
                                    case 'cancellato':
                                        $status_class = 'status-expired';
                                        $status_label = __('Cancelled', 'mioweb-agency');
                                        break;
                                    default:
                                        $status_class = '';
                                        $status_label = ucfirst($h->status);
                                }
                                ?>
                                <span class="mioweb-status <?php echo esc_html($status_class); ?>">
                                    <?php echo esc_html($status_label); ?>
                                </span>
                            </td>
                            <td>
                                <div class="mioweb-actions">
                                    <a href="?page=mioweb-hosting-form&id=<?php echo esc_html($h->id); ?>"
                                        class="button button-small">
                                        <?php esc_html_e('Edit', 'mioweb-agency'); ?>
                                    </a>

                                    <a href="<?php echo esc_url(wp_nonce_url(
                                                    admin_url('admin.php?page=mioweb-hosting&action=delete&id=' . $h->id),
                                                    'delete_hosting_' . $h->id
                                                )); ?>"
                                        class="button button-small mioweb-delete"
                                        onclick="return confirm('<?php esc_attr_e('Delete this hosting plan?', 'mioweb-agency'); ?>')">
                                        <?php esc_html_e('Delete', 'mioweb-agency'); ?>
                                    </a>

                                    <a href="?page=mioweb-manutenzioni-form&cliente_id=<?php echo esc_html($h->cliente_id); ?>&hosting_id=<?php echo esc_html($h->id); ?>"
                                        class="button button-small">
                                        <?php esc_html_e('Add Contract', 'mioweb-agency'); ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginazione -->
        <?php if ($hosting_list['pages'] > 1) : ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    $paginate_args = [
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $hosting_list['pages'],
                        'current' => $hosting_list['page']
                    ];
                    echo wp_kses_post(paginate_links($paginate_args));
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php
}
