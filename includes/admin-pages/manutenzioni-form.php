<?php

/**
 * Admin Page: Aggiungi/Modifica Manutenzione
 *
 * @package MioWebAgency
 */

// Evita accesso diretto
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Renderizza il form manutenzione
 */
function mioweb_render_manutenzioni_form()
{

    // Enqueue style direttamente qui
    wp_enqueue_style(
        'mioweb-manutenzioni-form',
        MIOWEB_PLUGIN_URL . 'includes/css/manutenzioni-form.css',
        [],
        MIOWEB_VERSION
    );

    $manutenzione_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;
    $manutenzione = $manutenzione_id ? mioweb_get_manutenzione($manutenzione_id) : null;

    // Salvataggio form
    if (isset($_POST['mioweb_save_manutenzione'])) {
        check_admin_referer('mioweb_save_manutenzione', 'mioweb_nonce');

        //$data = $_POST['manutenzione'];
        // Pulizia completa: rimuove gli slash e sanitizza ogni elemento dell'array 'cliente'
        $data = isset($_POST['manutenzione']) ? map_deep(wp_unslash((array) $_POST['manutenzione']), 'sanitize_text_field') : array();


        if ($manutenzione_id) {
            $result = mioweb_update_manutenzione($manutenzione_id, $data);
        } else {
            $result = mioweb_create_manutenzione($data);
        }

        if (! is_wp_error($result)) {
            if ($manutenzione_id) {
                wp_safe_redirect(admin_url('admin.php?page=mioweb-manutenzioni&success=1&action=updated'));
            } else {
                wp_safe_redirect(admin_url('admin.php?page=mioweb-manutenzioni&success=1&action=created'));
            }
            exit;
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result->get_error_message()) . '</p></div>';
        }
    }

    // Se abbiamo un ID e non abbiamo ancora caricato, ricarichiamo
    if ($manutenzione_id && ! $manutenzione) {
        $manutenzione = mioweb_get_manutenzione($manutenzione_id);
    }

    // Ottieni lista clienti per la select
    global $wpdb;

    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $clienti = $wpdb->get_results(
        "SELECT id, ragione_sociale, nome, cognome 
        FROM {$wpdb->prefix}mioweb_clienti 
        ORDER BY ragione_sociale, nome"
    );

    // Ottieni hosting per il cliente selezionato
    $hosting = [];
    if ($manutenzione && $manutenzione->cliente_id) {
        $hosting = $wpdb->get_results($wpdb->prepare(
            "SELECT id, nome_sito, dominio_principale 
            FROM {$wpdb->prefix}mioweb_hosting 
            WHERE cliente_id = %d AND status = 'attivo'
            ORDER BY nome_sito",
            $manutenzione->cliente_id
        ));
    } elseif ($cliente_id) {
        $hosting = $wpdb->get_results($wpdb->prepare(
            "SELECT id, nome_sito, dominio_principale 
            FROM {$wpdb->prefix}mioweb_hosting 
            WHERE cliente_id = %d AND status = 'attivo'
            ORDER BY nome_sito",
            $cliente_id
        ));
    }
    // phpcs:enable
?>

    <div class="wrap mioweb-manutenzione-form-wrap">
        <h1>
            <?php echo $manutenzione_id ? esc_html__('Edit Maintenance Contract', 'mioweb-agency') : esc_html__('Add New Maintenance Contract', 'mioweb-agency'); ?>
        </h1>

        <form method="post" action="" class="mioweb-form" id="mioweb-manutenzione-form">
            <?php wp_nonce_field('mioweb_save_manutenzione', 'mioweb_nonce'); ?>

            <div class="mioweb-form-layout">
                <!-- Colonna principale -->
                <div class="mioweb-form-main">
                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Client & Contract', 'mioweb-agency'); ?></h2>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="manutenzione_cliente_id">
                                    <?php esc_html_e('Client', 'mioweb-agency'); ?> *
                                </label>
                                <select id="manutenzione_cliente_id"
                                    name="manutenzione[cliente_id]"
                                    class="regular-text"
                                    required>
                                    <option value=""><?php esc_html_e('Select a client', 'mioweb-agency'); ?></option>
                                    <?php foreach ($clienti as $cliente) : ?>
                                        <option value="<?php echo esc_html($cliente->id); ?>"
                                            <?php selected(
                                                $manutenzione->cliente_id ?? $cliente_id,
                                                $cliente->id
                                            ); ?>>
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
                            </div>

                            <div class="mioweb-form-field">
                                <label for="manutenzione_hosting_id">
                                    <?php esc_html_e('Associated Hosting', 'mioweb-agency'); ?>
                                </label>
                                <select id="manutenzione_hosting_id" name="manutenzione[hosting_id]">
                                    <option value=""><?php esc_html_e('None', 'mioweb-agency'); ?></option>
                                    <?php if (! empty($hosting)) : ?>
                                        <?php foreach ($hosting as $h) : ?>
                                            <option value="<?php echo esc_html($h->id); ?>"
                                                <?php selected($manutenzione->hosting_id ?? 0, $h->id); ?>>
                                                <?php echo esc_html($h->nome_sito . ' (' . $h->dominio_principale . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('Optional: link this contract to a specific hosting plan', 'mioweb-agency'); ?>
                                </p>
                            </div>
                        </div>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field full-width">
                                <label for="manutenzione_nome_contratto">
                                    <?php esc_html_e('Contract Name', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="manutenzione_nome_contratto"
                                    name="manutenzione[nome_contratto]"
                                    value="<?php echo esc_attr($manutenzione->nome_contratto ?? ''); ?>"
                                    class="regular-text"
                                    placeholder="<?php esc_attr_e('e.g. Premium Maintenance 2026', 'mioweb-agency'); ?>">
                            </div>
                        </div>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="manutenzione_tipo">
                                    <?php esc_html_e('Contract Type', 'mioweb-agency'); ?>
                                </label>
                                <select id="manutenzione_tipo" name="manutenzione[tipo]">
                                    <option value="base" <?php selected($manutenzione->tipo ?? '', 'base'); ?>>
                                        <?php esc_html_e('Base', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="professional" <?php selected($manutenzione->tipo ?? '', 'professional'); ?>>
                                        <?php esc_html_e('Professional', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="premium" <?php selected($manutenzione->tipo ?? '', 'premium'); ?>>
                                        <?php esc_html_e('Premium', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="custom" <?php selected($manutenzione->tipo ?? '', 'custom'); ?>>
                                        <?php esc_html_e('Custom', 'mioweb-agency'); ?>
                                    </option>
                                </select>
                            </div>

                            <div class="mioweb-form-field">
                                <label for="manutenzione_stato">
                                    <?php esc_html_e('Status', 'mioweb-agency'); ?>
                                </label>
                                <select id="manutenzione_stato" name="manutenzione[stato]">
                                    <option value="attivo" <?php selected($manutenzione->stato ?? '', 'attivo'); ?>>
                                        <?php esc_html_e('Active', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="in_scadenza" <?php selected($manutenzione->stato ?? '', 'in_scadenza'); ?>>
                                        <?php esc_html_e('Expiring soon', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="sospeso" <?php selected($manutenzione->stato ?? '', 'sospeso'); ?>>
                                        <?php esc_html_e('Suspended', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="cancellato" <?php selected($manutenzione->stato ?? '', 'cancellato'); ?>>
                                        <?php esc_html_e('Cancelled', 'mioweb-agency'); ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Dates & Renewal', 'mioweb-agency'); ?></h2>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="manutenzione_data_inizio">
                                    <?php esc_html_e('Start Date', 'mioweb-agency'); ?> *
                                </label>
                                <input type="date"
                                    id="manutenzione_data_inizio"
                                    name="manutenzione[data_inizio]"
                                    value="<?php echo esc_attr($manutenzione->data_inizio ?? gmdate('Y-m-d')); ?>"
                                    class="regular-text"
                                    required>
                            </div>

                            <div class="mioweb-form-field">
                                <label for="manutenzione_data_fine">
                                    <?php esc_html_e('End Date', 'mioweb-agency'); ?>
                                </label>
                                <input type="date"
                                    id="manutenzione_data_fine"
                                    name="manutenzione[data_fine]"
                                    value="<?php echo esc_attr($manutenzione->data_fine ?? ''); ?>"
                                    class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('Leave empty for ongoing contracts', 'mioweb-agency'); ?>
                                </p>
                            </div>
                        </div>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="manutenzione_prossimo_rinnovo">
                                    <?php esc_html_e('Next Renewal Date', 'mioweb-agency'); ?>
                                </label>
                                <input type="date"
                                    id="manutenzione_prossimo_rinnovo"
                                    name="manutenzione[prossimo_rinnovo]"
                                    value="<?php echo esc_attr($manutenzione->prossimo_rinnovo ?? ''); ?>"
                                    class="regular-text">
                            </div>

                            <div class="mioweb-form-field">
                                <label for="manutenzione_ciclodi_rinnovo">
                                    <?php esc_html_e('Renewal Cycle', 'mioweb-agency'); ?>
                                </label>
                                <select id="manutenzione_ciclodi_rinnovo" name="manutenzione[ciclodi_rinnovo]">
                                    <option value="mensile" <?php selected($manutenzione->ciclodi_rinnovo ?? '', 'mensile'); ?>>
                                        <?php esc_html_e('Monthly', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="trimestrale" <?php selected($manutenzione->ciclodi_rinnovo ?? '', 'trimestrale'); ?>>
                                        <?php esc_html_e('Quarterly', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="semestrale" <?php selected($manutenzione->ciclodi_rinnovo ?? '', 'semestrale'); ?>>
                                        <?php esc_html_e('Semi-annual', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="annuale" <?php selected($manutenzione->ciclodi_rinnovo ?? '', 'annuale'); ?>>
                                        <?php esc_html_e('Annual', 'mioweb-agency'); ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Economic Information', 'mioweb-agency'); ?></h2>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="manutenzione_importo">
                                    <?php esc_html_e('Amount', 'mioweb-agency'); ?>
                                </label>
                                <input type="number"
                                    id="manutenzione_importo"
                                    name="manutenzione[importo]"
                                    value="<?php echo esc_attr($manutenzione->importo ?? '0'); ?>"
                                    class="regular-text"
                                    step="0.01"
                                    min="0">
                            </div>

                            <div class="mioweb-form-field">
                                <label for="manutenzione_valuta">
                                    <?php esc_html_e('Currency', 'mioweb-agency'); ?>
                                </label>
                                <select id="manutenzione_valuta" name="manutenzione[valuta]">
                                    <option value="EUR" <?php selected($manutenzione->valuta ?? '', 'EUR'); ?>>EUR</option>
                                    <option value="USD" <?php selected($manutenzione->valuta ?? '', 'USD'); ?>>USD</option>
                                    <option value="GBP" <?php selected($manutenzione->valuta ?? '', 'GBP'); ?>>GBP</option>
                                    <option value="CHF" <?php selected($manutenzione->valuta ?? '', 'CHF'); ?>>CHF</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="mioweb-form-sidebar">
                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Save', 'mioweb-agency'); ?></h2>

                        <div class="mioweb-form-actions">
                            <button type="submit" name="mioweb_save_manutenzione" class="button button-primary button-large">
                                <?php esc_html_e('Save Contract', 'mioweb-agency'); ?>
                            </button>

                            <a href="?page=mioweb-manutenzioni" class="button button-large">
                                <?php esc_html_e('Cancel', 'mioweb-agency'); ?>
                            </a>
                        </div>
                    </div>

                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Notes', 'mioweb-agency'); ?></h2>

                        <textarea id="manutenzione_note"
                            name="manutenzione[note]"
                            rows="8"
                            style="width: 100%;"><?php echo esc_textarea($manutenzione->note ?? ''); ?></textarea>

                        <p class="description">
                            <?php esc_html_e('Internal notes about this contract.', 'mioweb-agency'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        jQuery(document).ready(function($) {
            // Carica hosting quando cambia cliente
            $('#manutenzione_cliente_id').on('change', function() {
                var clienteId = $(this).val();
                var $hostingSelect = $('#manutenzione_hosting_id');

                if (!clienteId) {
                    $hostingSelect.html('<option value=""><?php esc_html_e('None', 'mioweb-agency'); ?></option>');
                    return;
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'mioweb_get_hosting_by_cliente',
                        cliente_id: clienteId,
                        nonce: '<?php echo esc_js(wp_create_nonce('mioweb_get_hosting')); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            var options = '<option value=""><?php esc_html_e('None', 'mioweb-agency'); ?></option>';
                            $.each(response.data, function(i, hosting) {
                                options += '<option value="' + hosting.id + '">' +
                                    hosting.nome_sito + ' (' + hosting.dominio_principale + ')</option>';
                            });
                            $hostingSelect.html(options);
                        }
                    }
                });
            });

            // Calcola prossimo rinnovo in base a data inizio e ciclo
            function calcolaProssimoRinnovo() {
                var dataInizio = $('#manutenzione_data_inizio').val();
                var ciclo = $('#manutenzione_ciclodi_rinnovo').val();

                if (!dataInizio || !ciclo) return;

                // Calcolo lato client (opzionale)
                var data = new Date(dataInizio);
                var oggi = new Date();

                while (data <= oggi) {
                    switch (ciclo) {
                        case 'mensile':
                            data.setMonth(data.getMonth() + 1);
                            break;
                        case 'trimestrale':
                            data.setMonth(data.getMonth() + 3);
                            break;
                        case 'semestrale':
                            data.setMonth(data.getMonth() + 6);
                            break;
                        case 'annuale':
                            data.setFullYear(data.getFullYear() + 1);
                            break;
                    }
                }

                var anno = data.getFullYear();
                var mese = String(data.getMonth() + 1).padStart(2, '0');
                var giorno = String(data.getDate()).padStart(2, '0');

                $('#manutenzione_prossimo_rinnovo').val(anno + '-' + mese + '-' + giorno);
            }

            $('#manutenzione_data_inizio, #manutenzione_ciclodi_rinnovo').on('change', calcolaProssimoRinnovo);
        });
    </script>


<?php
}

/**
 * AJAX: Ottieni hosting per cliente
 */
add_action('wp_ajax_mioweb_get_hosting_by_cliente', 'mioweb_ajax_get_hosting_by_cliente');
function mioweb_ajax_get_hosting_by_cliente()
{
    check_ajax_referer('mioweb_get_hosting', 'nonce');

    // $cliente_id = intval($_POST['cliente_id']);
    $cliente_id = isset($_POST['cliente_id']) ? absint(wp_unslash($_POST['cliente_id'])) : 0;

    // Se l'ID è 0 o non è valido, chiudiamo subito
    if (empty($cliente_id)) {
        wp_send_json_error(array('message' => 'ID cliente non valido'));
    }

    global $wpdb;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $hosting = $wpdb->get_results($wpdb->prepare(
        "SELECT id, nome_sito, dominio_principale 
        FROM {$wpdb->prefix}mioweb_hosting 
        WHERE cliente_id = %d AND status = 'attivo'
        ORDER BY nome_sito",
        $cliente_id
    ));

    wp_send_json_success($hosting);
}
