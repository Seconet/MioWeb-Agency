<?php

/**
 * Admin Page: Aggiungi/Modifica Hosting
 *
 * @package MioWebAgency
 */

// Evita accesso diretto
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Renderizza il form hosting
 */
function mioweb_render_hosting_form()
{

    // Enqueue style direttamente qui
    wp_enqueue_style(
        'mioweb-hosting-form',
        MIOWEB_PLUGIN_URL . 'includes/css/hosting-form.css',
        [],
        MIOWEB_VERSION
    );

    $hosting_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;
    $hosting = $hosting_id ? mioweb_get_hosting($hosting_id) : null;

    // Salvataggio form
    if (isset($_POST['mioweb_save_hosting'])) {
        check_admin_referer('mioweb_save_hosting', 'mioweb_nonce');

        //$data = $_POST['hosting'];

        // 1. Verifico che l'array esista
        // Puliamo l'intero array in un colpo solo prima di assegnarlo
        $hosting_post = isset( $_POST['hosting'] ) ? map_deep( wp_unslash( (array) $_POST['hosting'] ), 'sanitize_text_field' ) : array();

        // 2. Creo un array pulito (Sanitizzazione + Unslash)
        $data = array();

        if (! empty($hosting_post)) {
            foreach ($hosting_post as $key => $value) {
                // Puliamo la chiave e il valore (wp_unslash rimuove le sferzate aggiunte da WP)
                $clean_key   = sanitize_key($key);
                $clean_value = sanitize_text_field(wp_unslash($value));

                $data[$clean_key] = $clean_value;
            }
        }



        if ($hosting_id) {
            $result = mioweb_update_hosting($hosting_id, $data);
        } else {
            $result = mioweb_create_hosting($data);
        }

        if (! is_wp_error($result)) {
            if ($hosting_id) {
                wp_safe_redirect(admin_url('admin.php?page=mioweb-hosting&success=1&action=updated'));
            } else {
                wp_safe_redirect(admin_url('admin.php?page=mioweb-hosting&success=1&action=created'));
            }
            exit;
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result->get_error_message()) . '</p></div>';
        }
    }

    // Se abbiamo un ID e non abbiamo ancora caricato, ricarichiamo
    if ($hosting_id && ! $hosting) {
        $hosting = mioweb_get_hosting($hosting_id);
    }

    // Ottieni lista clienti per la select
    global $wpdb;

    // phpcs:disable WordPress.DB.DirectDatabaseQuery
    $clienti = $wpdb->get_results(
        "SELECT id, ragione_sociale, nome, cognome 
        FROM {$wpdb->prefix}mioweb_clienti 
        ORDER BY ragione_sociale, nome"
    );

    // Lista provider comuni
    $providers = [
        'Aruba',
        'SiteGround',
        'OVH',
        'Namecheap',
        'GoDaddy',
        'DigitalOcean',
        'AWS',
        'Google Cloud',
        'Microsoft Azure',
        'Netlify',
        'Vercel',
        'Seeweb',
        'Register.it',
        'Tophost',
        'Serverplan',
        'Keliweb',
        'Altro'
    ];
?>

    <div class="wrap mioweb-hosting-form-wrap">
        <h1>
            <?php echo $hosting_id ? esc_html__('Edit Hosting Plan', 'mioweb-agency') : esc_html__('Add New Hosting Plan', 'mioweb-agency'); ?>
        </h1>

        <form method="post" action="" class="mioweb-form" id="mioweb-hosting-form">
            <?php wp_nonce_field('mioweb_save_hosting', 'mioweb_nonce'); ?>

            <div class="mioweb-form-layout">
                <!-- Colonna principale -->
                <div class="mioweb-form-main">
                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Client & Site', 'mioweb-agency'); ?></h2>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="hosting_cliente_id">
                                    <?php esc_html_e('Client', 'mioweb-agency'); ?> *
                                </label>
                                <select id="hosting_cliente_id"
                                    name="hosting[cliente_id]"
                                    class="regular-text"
                                    required>
                                    <option value=""><?php esc_html_e('Select a client', 'mioweb-agency'); ?></option>
                                    <?php foreach ($clienti as $cliente) : ?>
                                        <option value="<?php echo esc_html($cliente->id); ?>"
                                            <?php selected(
                                                $hosting->cliente_id ?? $cliente_id,
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
                        </div>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="hosting_nome_sito">
                                    <?php esc_html_e('Site Name', 'mioweb-agency'); ?> *
                                </label>
                                <input type="text"
                                    id="hosting_nome_sito"
                                    name="hosting[nome_sito]"
                                    value="<?php echo esc_attr($hosting->nome_sito ?? ''); ?>"
                                    class="regular-text"
                                    placeholder="<?php esc_attr_e('e.g. Client Website', 'mioweb-agency'); ?>"
                                    required>
                            </div>

                            <div class="mioweb-form-field">
                                <label for="hosting_dominio_principale">
                                    <?php esc_html_e('Main Domain', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="hosting_dominio_principale"
                                    name="hosting[dominio_principale]"
                                    value="<?php echo esc_attr($hosting->dominio_principale ?? ''); ?>"
                                    class="regular-text"
                                    placeholder="example.com">
                            </div>
                        </div>
                    </div>

                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Hosting Details', 'mioweb-agency'); ?></h2>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="hosting_provider">
                                    <?php esc_html_e('Provider', 'mioweb-agency'); ?>
                                </label>
                                <select id="hosting_provider" name="hosting[provider]">
                                    <option value=""><?php esc_html_e('Select provider', 'mioweb-agency'); ?></option>
                                    <?php foreach ($providers as $p) : ?>
                                        <option value="<?php echo esc_attr($p); ?>"
                                            <?php selected($hosting->provider ?? '', $p); ?>>
                                            <?php echo esc_html($p); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mioweb-form-field">
                                <label for="hosting_piano">
                                    <?php esc_html_e('Plan', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="hosting_piano"
                                    name="hosting[piano]"
                                    value="<?php echo esc_attr($hosting->piano ?? ''); ?>"
                                    class="regular-text"
                                    placeholder="<?php esc_attr_e('e.g. Business, Pro, etc.', 'mioweb-agency'); ?>">
                            </div>
                        </div>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="hosting_ip_server">
                                    <?php esc_html_e('Server IP', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="hosting_ip_server"
                                    name="hosting[ip_server]"
                                    value="<?php echo esc_attr($hosting->ip_server ?? ''); ?>"
                                    class="regular-text"
                                    placeholder="xxx.xxx.xxx.xxx">
                            </div>

                            <div class="mioweb-form-field">
                                <label for="hosting_nameserver1">
                                    <?php esc_html_e('Nameserver 1', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="hosting_nameserver1"
                                    name="hosting[nameserver1]"
                                    value="<?php echo esc_attr($hosting->nameserver1 ?? ''); ?>"
                                    class="regular-text">
                            </div>

                            <div class="mioweb-form-field">
                                <label for="hosting_nameserver2">
                                    <?php esc_html_e('Nameserver 2', 'mioweb-agency'); ?>
                                </label>
                                <input type="text"
                                    id="hosting_nameserver2"
                                    name="hosting[nameserver2]"
                                    value="<?php echo esc_attr($hosting->nameserver2 ?? ''); ?>"
                                    class="regular-text">
                            </div>
                        </div>
                    </div>

                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Billing & Dates', 'mioweb-agency'); ?></h2>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="hosting_data_attivazione">
                                    <?php esc_html_e('Activation Date', 'mioweb-agency'); ?>
                                </label>
                                <input type="date"
                                    id="hosting_data_attivazione"
                                    name="hosting[data_attivazione]"
                                    value="<?php echo esc_attr($hosting->data_attivazione ?? gmdate('Y-m-d')); ?>"
                                    class="regular-text">
                            </div>

                            <div class="mioweb-form-field">
                                <label for="hosting_data_scadenza">
                                    <?php esc_html_e('Expiry Date', 'mioweb-agency'); ?>
                                </label>
                                <input type="date"
                                    id="hosting_data_scadenza"
                                    name="hosting[data_scadenza]"
                                    value="<?php echo esc_attr($hosting->data_scadenza ?? ''); ?>"
                                    class="regular-text">
                            </div>
                        </div>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="hosting_costo">
                                    <?php esc_html_e('Cost', 'mioweb-agency'); ?>
                                </label>
                                <input type="number"
                                    id="hosting_costo"
                                    name="hosting[costo]"
                                    value="<?php echo esc_attr($hosting->costo ?? '0'); ?>"
                                    class="regular-text"
                                    step="0.01"
                                    min="0">
                            </div>

                            <div class="mioweb-form-field">
                                <label for="hosting_valuta">
                                    <?php esc_html_e('Currency', 'mioweb-agency'); ?>
                                </label>
                                <select id="hosting_valuta" name="hosting[valuta]">
                                    <option value="EUR" <?php selected($hosting->valuta ?? '', 'EUR'); ?>>EUR</option>
                                    <option value="USD" <?php selected($hosting->valuta ?? '', 'USD'); ?>>USD</option>
                                    <option value="GBP" <?php selected($hosting->valuta ?? '', 'GBP'); ?>>GBP</option>
                                    <option value="CHF" <?php selected($hosting->valuta ?? '', 'CHF'); ?>>CHF</option>
                                </select>
                            </div>

                            <div class="mioweb-form-field">
                                <label for="hosting_ciclo_fatturazione">
                                    <?php esc_html_e('Billing Cycle', 'mioweb-agency'); ?>
                                </label>
                                <select id="hosting_ciclo_fatturazione" name="hosting[ciclo_fatturazione]">
                                    <option value="mensile" <?php selected($hosting->ciclo_fatturazione ?? '', 'mensile'); ?>>
                                        <?php esc_html_e('Monthly', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="trimestrale" <?php selected($hosting->ciclo_fatturazione ?? '', 'trimestrale'); ?>>
                                        <?php esc_html_e('Quarterly', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="semestrale" <?php selected($hosting->ciclo_fatturazione ?? '', 'semestrale'); ?>>
                                        <?php esc_html_e('Semi-annual', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="annuale" <?php selected($hosting->ciclo_fatturazione ?? '', 'annuale'); ?>>
                                        <?php esc_html_e('Annual', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="biennale" <?php selected($hosting->ciclo_fatturazione ?? '', 'biennale'); ?>>
                                        <?php esc_html_e('Biennial', 'mioweb-agency'); ?>
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field">
                                <label for="hosting_status">
                                    <?php esc_html_e('Status', 'mioweb-agency'); ?>
                                </label>
                                <select id="hosting_status" name="hosting[status]">
                                    <option value="attivo" <?php selected($hosting->status ?? '', 'attivo'); ?>>
                                        <?php esc_html_e('Active', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="sospeso" <?php selected($hosting->status ?? '', 'sospeso'); ?>>
                                        <?php esc_html_e('Suspended', 'mioweb-agency'); ?>
                                    </option>
                                    <option value="cancellato" <?php selected($hosting->status ?? '', 'cancellato'); ?>>
                                        <?php esc_html_e('Cancelled', 'mioweb-agency'); ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Credentials (Encrypted)', 'mioweb-agency'); ?></h2>
                        <p class="description">
                            <?php esc_html_e('This data is stored encrypted in the database.', 'mioweb-agency'); ?>
                        </p>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field full-width">
                                <label for="hosting_credenziali_ftp">
                                    <?php esc_html_e('FTP/SSH Credentials', 'mioweb-agency'); ?>
                                </label>
                                <textarea id="hosting_credenziali_ftp"
                                    name="hosting[credenziali_ftp]"
                                    rows="3"
                                    style="width: 100%;"
                                    placeholder="<?php esc_attr_e('Host, username, password, port...', 'mioweb-agency'); ?>"><?php echo esc_textarea($hosting->credenziali_ftp ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="mioweb-form-row">
                            <div class="mioweb-form-field full-width">
                                <label for="hosting_credenziali_admin">
                                    <?php esc_html_e('Admin Panel Credentials', 'mioweb-agency'); ?>
                                </label>
                                <textarea id="hosting_credenziali_admin"
                                    name="hosting[credenziali_admin]"
                                    rows="3"
                                    style="width: 100%;"
                                    placeholder="<?php esc_attr_e('URL, username, password...', 'mioweb-agency'); ?>"><?php echo esc_textarea($hosting->credenziali_admin ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="mioweb-form-sidebar">
                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Save', 'mioweb-agency'); ?></h2>

                        <div class="mioweb-form-actions">
                            <button type="submit" name="mioweb_save_hosting" class="button button-primary button-large">
                                <?php esc_html_e('Save Hosting', 'mioweb-agency'); ?>
                            </button>

                            <a href="?page=mioweb-hosting" class="button button-large">
                                <?php esc_html_e('Cancel', 'mioweb-agency'); ?>
                            </a>
                        </div>
                    </div>

                    <div class="mioweb-form-section">
                        <h2><?php esc_html_e('Technical Notes', 'mioweb-agency'); ?></h2>

                        <textarea id="hosting_note_tecniche"
                            name="hosting[note_tecniche]"
                            rows="8"
                            style="width: 100%;"
                            placeholder="<?php esc_attr_e('PHP version, database info, special configurations...', 'mioweb-agency'); ?>"><?php echo esc_textarea($hosting->note_tecniche ?? ''); ?></textarea>
                    </div>

                    <?php if ($hosting_id) :
                        // Conta manutenzioni collegate
                        $manutenzioni_count = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}mioweb_manutenzioni WHERE hosting_id = %d",
                            $hosting_id
                        ));
                    ?>
                        <div class="mioweb-form-section">
                            <h2><?php esc_html_e('Linked Contracts', 'mioweb-agency'); ?></h2>

                            <p>
                                <strong><?php echo esc_html($manutenzioni_count); ?></strong>
                                <?php esc_html_e('maintenance contracts linked', 'mioweb-agency'); ?>
                            </p>

                            <?php if ($manutenzioni_count > 0) : ?>
                                <a href="?page=mioweb-manutenzioni&hosting_id=<?php echo esc_html($hosting_id); ?>" class="button">
                                    <?php esc_html_e('View Contracts', 'mioweb-agency'); ?>
                                </a>
                            <?php else : ?>
                                <a href="?page=mioweb-manutenzioni-form&cliente_id=<?php echo esc_html($hosting->cliente_id); ?>&hosting_id=<?php echo esc_html($hosting_id); ?>" class="button">
                                    <?php esc_html_e('Add Contract', 'mioweb-agency'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>


<?php
}
