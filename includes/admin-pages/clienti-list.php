<?php

/**
 * Admin Page: Lista Clienti
 *
 * @package MioWebAgency
 */

// Evita accesso diretto
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Renderizza la pagina lista clienti
 */
function mioweb_render_clienti_list()
{
    // Enqueue style direttamente qui
    wp_enqueue_style('mioweb-clienti-list', MIOWEB_PLUGIN_URL . 'includes/css/clienti-list.css', [], MIOWEB_VERSION);

    //  MESSAGGI DI FEEDBACK - QUI all'inizio
    if (isset($_GET['success']) && isset($_GET['action'])) {
        if ($_GET['action'] === 'created') {
            echo '<div class="notice notice-success is-dismissible"><p>' .
                esc_html__('Client created successfully.', 'mioweb-agency') .
                '</p></div>';
        }
        if ($_GET['action'] === 'updated') {
            echo '<div class="notice notice-success is-dismissible"><p>' .
                esc_html__('Client updated successfully.', 'mioweb-agency') .
                '</p></div>';
        }
        if ($_GET['action'] === 'deleted') {
            echo '<div class="notice notice-success is-dismissible"><p>' .
                esc_html__('Client deleted successfully.', 'mioweb-agency') .
                '</p></div>';
        }
    }
    // Gestione azioni
    // 1. Estraiamo e sanitizziamo i dati una volta sola
    $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : '';
    $id     = isset($_GET['id']) ? absint($_GET['id']) : 0;
    $nonce  = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

    // 2. Gestione azioni con variabili pulite
    if ('delete' === $action && $id > 0) {
        // Verifichiamo il nonce usando le variabili già pulite
        if (wp_verify_nonce($nonce, 'delete_cliente_' . $id)) {

            mioweb_delete_cliente($id);

            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Client deleted successfully.', 'mioweb-agency') . '</p></div>';
        } else {
            // Opzionale: un messaggio di errore se il nonce fallisce
            wp_die(esc_html__('Security check failed. Please try again.', 'mioweb-agency'));
        }
    }

    /*if (isset($_GET['action']) && isset($_GET['id'])) {
        if ($_GET['action'] === 'delete' && wp_verify_nonce($_GET['_wpnonce'], 'delete_cliente_' . $_GET['id'])) {
            mioweb_delete_cliente(intval($_GET['id']));
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Client deleted successfully.', 'mioweb-agency') . '</p></div>';
        }
    }
    */

    // Parametri di ricerca
    $search = isset($_GET['s']) ? sanitize_text_field( wp_unslash($_GET['s']) ) : '';
    $tipo = isset($_GET['tipo']) ? sanitize_text_field( wp_unslash($_GET['tipo']) ) : '';
    $page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

    $clienti = mioweb_get_clienti([
        'search' => $search,
        'tipo' => $tipo,
        'page' => $page,
        'per_page' => 20
    ]);
?>
    <div class="wrap mioweb-clienti-wrap">
        <h1 class="wp-heading-inline">
            <?php esc_html_e('Clients', 'mioweb-agency'); ?>
        </h1>

        <a href="?page=mioweb-cliente-form" class="page-title-action">
            <?php esc_html_e('Add New Client', 'mioweb-agency'); ?>
        </a>

        <hr class="wp-header-end">

        <!-- Filtri e ricerca -->
        <div class="mioweb-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="mioweb-clienti">

                <div class="mioweb-filter-row">
                    <select name="tipo">
                        <option value=""><?php esc_html_e('All types', 'mioweb-agency'); ?></option>
                        <option value="azienda" <?php selected($tipo, 'azienda'); ?>>
                            <?php esc_html_e('Companies', 'mioweb-agency'); ?>
                        </option>
                        <option value="privato" <?php selected($tipo, 'privato'); ?>>
                            <?php esc_html_e('Individuals', 'mioweb-agency'); ?>
                        </option>
                        <option value="ente" <?php selected($tipo, 'ente'); ?>>
                            <?php esc_html_e('Public Entities', 'mioweb-agency'); ?>
                        </option>
                        <option value="associazione" <?php selected($tipo, 'associazione'); ?>>
                            <?php esc_html_e('Associations', 'mioweb-agency'); ?>
                        </option>
                    </select>

                    <input type="text"
                        name="s"
                        placeholder="<?php esc_attr_e('Search clients...', 'mioweb-agency'); ?>"
                        value="<?php echo esc_attr($search); ?>">

                    <button type="submit" class="button">
                        <?php esc_html_e('Filter', 'mioweb-agency'); ?>
                    </button>

                    <a href="?page=mioweb-clienti" class="button">
                        <?php esc_html_e('Reset', 'mioweb-agency'); ?>
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabella clienti -->
        <table class="wp-list-table widefat fixed striped mioweb-table">
            <thead>
                <tr>
                    <th scope="col" width="50">ID</th>
                    <th scope="col"><?php esc_html_e('Name / Company', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Type', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('VAT / Fiscal Code', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Email', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Phone', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Active Hosting', 'mioweb-agency'); ?></th>
                    <th scope="col"><?php esc_html_e('Actions', 'mioweb-agency'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clienti['items'])) : ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">
                            <?php esc_html_e('No clients found.', 'mioweb-agency'); ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($clienti['items'] as $cliente) :
                        $stats = mioweb_get_cliente_stats($cliente->id);
                    ?>
                        <tr>
                            <td><?php echo esc_html($cliente->id); ?></td>
                            <td>
                                <strong>
                                    <a href="?page=mioweb-cliente-form&id=<?php echo esc_html($cliente->id); ?>">
                                        <?php
                                        if (! empty($cliente->ragione_sociale)) {
                                            echo esc_html($cliente->ragione_sociale);
                                        } elseif (! empty($cliente->nome) || ! empty($cliente->cognome)) {
                                            echo esc_html(trim($cliente->nome . ' ' . $cliente->cognome));
                                        } else {
                                            echo '#' . esc_html($cliente->id) . ' ' . esc_html__('(no name)', 'mioweb-agency');
                                        }
                                        ?>
                                    </a>
                                </strong>
                            </td>
                            <td>
                                <?php
                                $tipi = [
                                    'azienda' => __('Company', 'mioweb-agency'),
                                    'privato' => __('Individual', 'mioweb-agency'),
                                    'ente' => __('Public Entity', 'mioweb-agency'),
                                    'associazione' => __('Association', 'mioweb-agency')
                                ];
                                echo esc_html($tipi[$cliente->tipo] ?? $cliente->tipo);
                                ?>
                            </td>
                            <td>
                                <?php
                                if ($cliente->piva) {
                                    echo esc_html($cliente->piva);
                                } elseif ($cliente->cf) {
                                    echo esc_html($cliente->cf);
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="mailto:<?php echo esc_attr($cliente->email); ?>">
                                    <?php echo esc_html($cliente->email); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo esc_html($cliente->telefono ?: $cliente->cellulare ?: '—'); ?>
                            </td>
                            <td>
                                <span class="mioweb-badge">
                                    <?php echo esc_html($stats['hosting_attivi']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="mioweb-actions">
                                    <a href="?page=mioweb-cliente-form&id=<?php echo esc_attr($cliente->id); ?>"
                                        class="button button-small">
                                        <?php esc_html_e('Edit', 'mioweb-agency'); ?>
                                    </a>

                                    <a href="<?php echo esc_url(
                                                    admin_url('admin.php?page=mioweb-clienti&action=delete&id=' . $cliente->id),
                                                    'delete_cliente_' . $cliente->id
                                                ); ?>"
                                        class="button button-small mioweb-delete"
                                        onclick="return confirm('<?php esc_attr_e('Delete this client? This will also delete all their hosting and maintenance contracts.', 'mioweb-agency'); ?>')">
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
        <?php if ($clienti['pages'] > 1) : ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    $paginate_args = [
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $clienti['pages'],
                        'current' => $clienti['page']
                    ];
                    echo wp_kses_post(paginate_links($paginate_args));
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>


<?php
}
