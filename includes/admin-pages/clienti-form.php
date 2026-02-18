<?php

/**
 * Admin Page: Aggiungi/Modifica Cliente
 *
 * @package MioWebAgency
 */

// Evita accesso diretto
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Renderizza il form cliente
 */
function mioweb_render_cliente_form()
{
    // Enqueue style direttamente qui
    wp_enqueue_style(
        'mioweb-clienti-form',
        MIOWEB_PLUGIN_URL . 'includes/css/clienti-form.css',
        [],
        MIOWEB_VERSION
    );

    $cliente_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $cliente = $cliente_id ? mioweb_get_cliente($cliente_id) : null;

    // Salvataggio form
    if (isset($_POST['mioweb_save_cliente'])) {
        check_admin_referer('mioweb_save_cliente', 'mioweb_nonce');

        //$data = $_POST['cliente'];
        // Pulizia completa: rimuove gli slash e sanitizza ogni elemento dell'array 'cliente'
        $data = isset( $_POST['cliente'] ) ? map_deep( wp_unslash( (array) $_POST['cliente'] ), 'sanitize_text_field' ) : array();
        









        if ($cliente_id) {
            $result = mioweb_update_cliente($cliente_id, $data);
        } else {
            $result = mioweb_create_cliente($data);
        }

        // Redirect alla lista con messaggio 
        if (! is_wp_error($result)) {
            if ($cliente_id) {
                wp_safe_redirect(admin_url('admin.php?page=mioweb-clienti&success=1&action=updated'));
            } else {
                wp_safe_redirect(admin_url('admin.php?page=mioweb-clienti&success=1&action=created'));
            }
            exit;
        }

        if (! is_wp_error($result) && ! isset($_GET['success'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
            // Ricarica i dati
            $cliente = mioweb_get_cliente($cliente_id);
        } else if (is_wp_error($result)) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result->get_error_message()) . '</p></div>';
        }
    }

    // Gestione messaggio di successo dopo redirect
    if (isset($_GET['success']) && $_GET['success'] == 1) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Client created successfully.', 'mioweb-agency') . '</p></div>';
        // Ricarica i dati dopo il redirect
        if ($cliente_id) {
            $cliente = mioweb_get_cliente($cliente_id);
        }
    }
    // Se abbiamo un ID e non abbiamo ancora caricato il cliente, ricarichiamo
    if ($cliente_id && ! $cliente) {
        $cliente = mioweb_get_cliente($cliente_id);
    }
?>

    <div class="wrap mioweb-cliente-form-wrap">
        <h1>
            <?php echo $cliente_id ? esc_html__('Edit Client', 'mioweb-agency') : esc_html__('Add New Client', 'mioweb-agency'); ?>
        </h1>

        <form method="post" action="" class="mioweb-form">
            <?php wp_nonce_field('mioweb_save_cliente', 'mioweb_nonce'); ?>

            <div class="mioweb-form-layout">
                <!-- Colonna principale -->
                <div class="mioweb-form-main">
                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Basic Information', 'mioweb-agency'); ?></h2>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field full-width">
                                <label for="cliente_ragione_sociale">
                                    <?php esc_html_e('Company Name', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="cliente_ragione_sociale"
                                    name="cliente[ragione_sociale]"
                                    value="<?php echo esc_attr($cliente->ragione_sociale ?? ''); ?>"
                                    class="regular-text">
                            </div>
                        </div>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="cliente_nome">
                                    <?php esc_html_e('First Name', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="cliente_nome"
                                    name="cliente[nome]"
                                    value="<?php echo esc_attr($cliente->nome ?? ''); ?>"
                                    class="regular-text">
                            </div>

                            <div class="mioweb-form-field">
                                <label for="cliente_cognome">
                                    <?php esc_html_e('Last Name', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="cliente_cognome"
                                    name="cliente[cognome]"
                                    value="<?php echo esc_attr($cliente->cognome ?? ''); ?>"
                                    class="regular-text">
                            </div>
                        </div>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="cliente_tipo">
                                    <?php esc_html_e('Client Type', 'mioweb-agency'); ?>
                                </label>
                                <select id="cliente_tipo" name="cliente[tipo]">
                                    <option value="privato" <?php selected($cliente->tipo ?? '', 'privato'); ?>>
                                        <?php esc_html_e('Individual', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="azienda" <?php selected($cliente->tipo ?? '', 'azienda'); ?>>
                                        <?php esc_html_e('Company', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="ente" <?php selected($cliente->tipo ?? '', 'ente'); ?>>
                                        <?php esc_html_e('Public Entity', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="associazione" <?php selected($cliente->tipo ?? '', 'associazione'); ?>>
                                        <?php esc_html_e('Association', 'mioweb-agency'); ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Tax Information', 'mioweb-agency'); ?></h2>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="cliente_piva">
                                    <?php esc_html_e('VAT Number', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="cliente_piva"
                                    name="cliente[piva]"
                                    value="<?php echo esc_attr($cliente->piva ?? ''); ?>"
                                    class="regular-text">
                            </div>

                            <div class="mioweb-form-field">
                                <label for="cliente_cf">
                                    <?php esc_html_e('Fiscal Code', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="cliente_cf"
                                    name="cliente[cf]"
                                    value="<?php echo esc_attr($cliente->cf ?? ''); ?>"
                                    class="regular-text">
                            </div>
                        </div>
                    </div>

                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Contacts', 'mioweb-agency'); ?></h2>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="cliente_email">
                                    <?php esc_html_e('Email', 'mioweb-agency'); ?> *
                                </label>
                                <input type="email"
                                    id="cliente_email"
                                    name="cliente[email]"
                                    value="<?php echo esc_attr($cliente->email ?? ''); ?>"
                                    class="regular-text"
                                    required>
                            </div>

                            <div class="mioweb-form-field">
                                <label for="cliente_pec">
                                    <?php esc_html_e('Certified Email (PEC)', 'mioweb-agency'); ?>
                                </label>
                                <input type="email"
                                    id="cliente_pec"
                                    name="cliente[pec]"
                                    value="<?php echo esc_attr($cliente->pec ?? ''); ?>"
                                    class="regular-text">
                            </div>
                        </div>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="cliente_telefono">
                                    <?php esc_html_e('Phone', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="cliente_telefono"
                                    name="cliente[telefono]"
                                    value="<?php echo esc_attr($cliente->telefono ?? ''); ?>"
                                    class="regular-text">
                            </div>

                            <div class="mioweb-form-field">
                                <label for="cliente_cellulare">
                                    <?php esc_html_e('Mobile', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="cliente_cellulare"
                                    name="cliente[cellulare]"
                                    value="<?php echo esc_attr($cliente->cellulare ?? ''); ?>"
                                    class="regular-text">
                            </div>
                        </div>
                    </div>

                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Address', 'mioweb-agency'); ?></h2>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field full-width">
                                <label for="cliente_indirizzo">
                                    <?php esc_html_e('Street Address', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="cliente_indirizzo"
                                    name="cliente[indirizzo]"
                                    value="<?php echo esc_attr($cliente->indirizzo ?? ''); ?>"
                                    class="regular-text"
                                    style="width: 100%;">
                            </div>
                        </div>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="cliente_citta">
                                    <?php esc_html_e('City', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="cliente_citta"
                                    name="cliente[citta]"
                                    value="<?php echo esc_attr($cliente->citta ?? ''); ?>"
                                    class="regular-text">
                            </div>

                            <div class="mioweb-form-field">
                                <label for="cliente_cap">
                                    <?php esc_html_e('ZIP Code', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="cliente_cap"
                                    name="cliente[cap]"
                                    value="<?php echo esc_attr($cliente->cap ?? ''); ?>"
                                    class="regular-text">
                            </div>

                            <div class="mioweb-form-field">
                                <label for="cliente_provincia">
                                    <?php esc_html_e('Province', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="cliente_provincia"
                                    name="cliente[provincia]"
                                    value="<?php echo esc_attr($cliente->provincia ?? ''); ?>"
                                    class="regular-text"
                                    maxlength="2"
                                    placeholder="RM">
                            </div>

                            <div class="mioweb-form-field">
                                <label for="cliente_nazione">
                                    <?php esc_html_e('Country', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="cliente_nazione"
                                    name="cliente[nazione]"
                                    value="<?php echo esc_attr($cliente->nazione ?? 'IT'); ?>"
                                    class="regular-text"
                                    maxlength="2"
                                    placeholder="IT">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="mioweb-form-sidebar">
                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Save', 'mioweb-agency'); ?></h2>

                        <div class="mioweb-form-actions">
                            <button type="submit" name="mioweb_save_cliente" class="button button-primary button-large">
                                <?php esc_html_e('Save Client', 'mioweb-agency'); ?>
                            </button>

                            <a href="?page=mioweb-clienti" class="button button-large">
                                <?php esc_html_e('Cancel', 'mioweb-agency'); ?>
                            </a>
                        </div>
                    </div>

                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Private Notes', 'mioweb-agency'); ?></h2>

                        <textarea id="cliente_note"
                            name="cliente[note]"
                            rows="8"
                            style="width: 100%;"><?php echo esc_textarea($cliente->note ?? ''); ?></textarea>

                        <p class="description">
                            <?php esc_html_e('Internal notes, not visible to the client.', 'mioweb-agency'); ?>
                        </p>
                    </div>

                    <?php if ($cliente_id) : ?>
                        <div class="mioweb-form-section">
                            <h2><?php esc_html_e('Statistics', 'mioweb-agency'); ?></h2>

                            <?php $stats = mioweb_get_cliente_stats($cliente_id); ?>

                            <ul class="mioweb-stats-list">
                                <?php if (isset($cliente->created_at) && $cliente->created_at) : ?>
                                    <li>
                                        <strong><?php esc_html_e('Created:', 'mioweb-agency'); ?></strong>
                                        <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $cliente->created_at ) ) ); ?>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <strong><?php esc_html_e('Active hosting:', 'mioweb-agency'); ?></strong>
                                    <?php echo esc_html($stats['hosting_attivi']); ?>
                                </li>
                                <li>
                                    <strong><?php esc_html_e('Active maintenance:', 'mioweb-agency'); ?></strong>
                                    <?php echo esc_html($stats['manutenzioni_attive']); ?>
                                </li>
                                <?php if ($stats['prossima_scadenza']) : ?>
                                    <li>
                                        <strong><?php esc_html_e('Next renewal:', 'mioweb-agency'); ?></strong>
                                        <?php echo esc_html( date_i18n(get_option('date_format'), strtotime($stats['prossima_scadenza']->prossimo_rinnovo)) ); ?>
                                    </li>
                                <?php endif; ?>
                            </ul>

                            <div class="mioweb-quick-actions">
                                <a href="?page=mioweb-hosting-form&cliente_id=<?php echo esc_html( $cliente_id ); ?>" class="button">
                                    <?php esc_html_e('Add Hosting', 'mioweb-agency'); ?>
                                </a>
                                <a href="?page=mioweb-manutenzioni-form&cliente_id=<?php echo esc_html( $cliente_id ); ?>" class="button">
                                    <?php esc_html_e('Add Maintenance', 'mioweb-agency'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>


<?php
}
