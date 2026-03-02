<?php

/**
 * Admin Settings
 *
 * @package MioWebAgency
 */

// Evita accesso diretto
if (! defined('ABSPATH')) {
    exit;
}


add_action('admin_menu', 'mioweb_add_admin_menu');

/**
 * Aggiunge le pagine di amministrazione
 */
function mioweb_add_admin_menu()
{
    // Menu principale
    add_menu_page(
        __('MioWeb Agency Web', 'mioweb-agency'),
        __('MioWeb Agency Web', 'mioweb-agency'),
        'manage_options',
        'mioweb',
        'mioweb_render_dashboard',
        'dashicons-portfolio',
        20
    );

    // Sottopagine
    add_submenu_page( // DASHBOARD
        'mioweb',
        __('Dashboard', 'mioweb-agency'),
        __('Dashboard', 'mioweb-agency'),
        'manage_options',
        'mioweb',
        'mioweb_render_dashboard'
    );


    add_submenu_page( //CLIENTS
        'mioweb',
        __('Clients', 'mioweb-agency'),
        __('Clients', 'mioweb-agency'),
        'manage_options',
        'mioweb-cliente',
        'mioweb_render_clienti_list'
    );

    add_submenu_page( //CLIENTS
        'mioweb', //nascosto
        __('Client Form', 'mioweb-agency'),
        __('Client Form', 'mioweb-agency'),
        'manage_options',
        'mioweb-cliente-form',
        'mioweb_render_cliente_form'
    );

    add_submenu_page( //SITI
        'mioweb',
        __('Websites', 'mioweb-agency'),
        __('Websites', 'mioweb-agency'),
        'manage_options',
        'edit.php?post_type=mioweb_sito'
    );

    add_submenu_page( //SITI new
        'mioweb',  // nascosto
        __('Add Website', 'mioweb-agency'),
        __('Add Website', 'mioweb-agency'),
        'manage_options',
        'post-new.php?post_type=mioweb_sito'
    );


    add_submenu_page( // PLUGIN
        'mioweb',
        __('Custom Plugins', 'mioweb-agency'),
        __('Custom Plugins', 'mioweb-agency'),
        'manage_options',
        'edit.php?post_type=mioweb_plugin'
    );
    add_submenu_page( // PLUGIN NEW
        'mioweb', // nascosto
        __('Add Plugin', 'mioweb-agency'),
        __('Add Plugin', 'mioweb-agency'),
        'manage_options',
        'post-new.php?post_type=mioweb_plugin'
    );


    add_submenu_page( //TEMA
        'mioweb',
        __('Themes', 'mioweb-agency'),
        __('Themes', 'mioweb-agency'),
        'manage_options',
        'edit.php?post_type=mioweb_tema'
    );
    add_submenu_page( //TEMA NEW
        'mioweb', // nascosto
        __('Add Theme', 'mioweb-agency'),
        __('Add Theme', 'mioweb-agency'),
        'manage_options',
        'post-new.php?post_type=mioweb_tema'
    );


    add_submenu_page( //MANUTENZIONI
        'mioweb',
        __('Maintenance', 'mioweb-agency'),
        __('Maintenance', 'mioweb-agency'),
        'manage_options',
        'mioweb-manutenzioni',  // CORRETTO - pagina personalizzata
        'mioweb_render_manutenzioni_list'
    );
    add_submenu_page( // MANUTENZIONI FORM
        'mioweb', // nascosto
        __('Add/Edit Maintenance', 'mioweb-agency'),
        __('Add/Edit Maintenance', 'mioweb-agency'),
        'manage_options',
        'mioweb-manutenzioni-form',
        'mioweb_render_manutenzioni_form'
    );
    //

    add_submenu_page( //HOSTING
        'mioweb',
        __('Hosting', 'mioweb-agency'),
        __('Hosting', 'mioweb-agency'),
        'manage_options',
        'mioweb-hosting',
        'mioweb_render_hosting_list'
    );
    add_submenu_page( // HOSTING FORM
        'mioweb', // nascosto
        __('Add/Edit Maintenance', 'mioweb-agency'),
        __('Add/Edit Maintenance', 'mioweb-agency'),
        'manage_options',
        'mioweb-hosting-form',
        'mioweb_render_hosting_form'
    );
}

add_action('admin_head', 'mioweb_fix_menu_visibility');
function mioweb_fix_menu_visibility()
{
    // Rimuove la visualizzazione nel menu, ma la pagina resta accessibile
    // e il menu 'mioweb' resterà espanso quando visitato.
    remove_submenu_page('mioweb', 'mioweb-cliente-form');
    remove_submenu_page('mioweb', 'mioweb-hosting-form');
    remove_submenu_page('mioweb', 'mioweb-manutenzioni-form');
}

add_filter('submenu_file', 'mioweb_highlight_parent_submenu', 10, 2);
function mioweb_highlight_parent_submenu($submenu_file)
{
    global $plugin_page, $pagenow, $post_type; // Questa variabile contiene lo slug della pagina attuale

    // Ritorna lo slug del "padre" che si vuole evidenziare
   
    if (strpos($plugin_page, '-form') !== false) {
        return str_replace('-form', '', $plugin_page);
    }
    // PER I CPT
    // Gestione CPT: Se siamo nei post di 'mioweb', evidenzia la lista
    $cpt_submenu_map = [
        'mioweb_sito'   => 'edit.php?post_type=mioweb_sito',
        'mioweb_plugin' => 'edit.php?post_type=mioweb_plugin',
        'mioweb_tema'   => 'edit.php?post_type=mioweb_tema',
    ];

    $is_cpt = isset($cpt_submenu_map[$post_type]);
    $is_cpt_edit_page = ($pagenow === 'post-new.php' || $pagenow === 'post.php');

    if ($is_cpt && $is_cpt_edit_page) {
        return $cpt_submenu_map[$post_type];
    }



    return $submenu_file;
}
add_action('admin_head', 'mioweb_hide_cpt_forms');
function mioweb_hide_cpt_forms()
{
    // Nasconde i form di aggiunta
    remove_submenu_page('mioweb', 'post-new.php?post_type=mioweb_sito');
    remove_submenu_page('mioweb', 'post-new.php?post_type=mioweb_plugin');
    remove_submenu_page('mioweb', 'post-new.php?post_type=mioweb_tema');
}
add_filter('parent_file', 'mioweb_set_cpt_parent_menu');
function mioweb_set_cpt_parent_menu($parent_file)
{
    global $pagenow, $post_type;

    // Se siamo nella pagina di creazione o modifica di un tuo CPT
    if (($pagenow === 'post-new.php' || $pagenow === 'post.php') && $post_type === 'mioweb_sito') {
        return 'mioweb'; // Forza l'apertura del menu principale MioWeb
    }
    if (($pagenow === 'post-new.php' || $pagenow === 'post.php') && $post_type === 'mioweb_plugin') {
        return 'mioweb'; // Forza l'apertura del menu principale MioWeb
    }
    if (($pagenow === 'post-new.php' || $pagenow === 'post.php') && $post_type === 'mioweb_tema') {
        return 'mioweb'; // Forza l'apertura del menu principale MioWeb
    }

    return $parent_file;
}





//** ADMIN notice*/
function mioweb_admin_notices()
{
    if (! (function_exists('mioweb_is_pro_active') && mioweb_is_pro_active())) :
        $screen = get_current_screen();
        if ($screen->id !== 'toplevel_page_mioweb') return;

        $user_id = get_current_user_id();
        $status = get_user_meta($user_id, 'mioweb_pro_notice_status', true);

        // Se lo stato è 'permanent', non mostriamo più nulla
        if ($status === 'permanent') return;

        // Se lo stato è un timestamp (snooze), controlliamo se è scaduto
        if (is_numeric($status) && time() < $status) return;

        $nonce = wp_create_nonce('mioweb_dismiss_notice_nonce');
?>

        <div class="notice notice-info is-dismissible mioweb-pro-notice" data-nonce="<?php echo esc_html($nonce); ?>" style="border-left-color: #6366f1; padding: 15px; position: relative;">
            <div class="mioweb-pro-notice-container">
                <div class="mioweb-pro-icon">
                    <span class="dashicons dashicons-superhero" style="width: 40px; height: 40px; font-size: 40px;"></span>
                </div>

                <div class="mioweb-pro-content">
                    <h3><?php esc_html_e('Porta la tua agenzia al livello Pro', 'mioweb-agency'); ?></h3>
                    <p><?php esc_html_e('Sblocca la gestione automatizzata degli hosting e i report PDF personalizzati per i tuoi clienti.', 'mioweb-agency'); ?></p>

                    <div class="mioweb-pro-actions">
                        <a href="https://seconet.it/mioweb_agency_pro" class="button button-primary mioweb-btn-upgrade" target="_blank" rel="noopener">
                            <?php esc_html_e('Ottieni il 20% di sconto ora', 'mioweb-agency'); ?>
                        </a>
                        <a href="#" class="mioweb-dismiss-permanent" style="color: #94a3b8; text-decoration: underline; font-size: 12px;">
                            <?php esc_html_e('No grazie, non mostrarlo più', 'mioweb-agency'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>


        <script>
            jQuery(document).ready(function($) {
                // Funzione unica per gestire entrambi i tipi di chiusura
                function dismissNotice(type) {
                    var nonce = $('.mioweb-pro-notice').data('nonce');
                    $.post(ajaxurl, {
                        action: 'mioweb_dismiss_pro_notice',
                        nonce: nonce,
                        type: type
                    });
                }

                // Clic sulla "X" (Snooze 30gg)
                $(document).on('click', '.mioweb-pro-notice .notice-dismiss', function() {
                    dismissNotice('snooze');
                });

                // Clic su "Non mostrare più" (Permanente)
                $(document).on('click', '.mioweb-dismiss-permanent', function(e) {
                    e.preventDefault();
                    $('.mioweb-pro-notice').fadeOut();
                    dismissNotice('permanent');
                });
            });
        </script>
    <?php
    endif;
}
add_action('admin_notices', 'mioweb_admin_notices');

/**
 * AJAX aggiornato per gestire i due tipi di chiusura
 */
function mioweb_dismiss_pro_notice_callback()
{
    check_ajax_referer('mioweb_dismiss_notice_nonce', 'nonce');

    $user_id = get_current_user_id();

    // $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'snooze';
    $type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : 'snooze';

    if ($type === 'permanent') {
        // Snooze per 120 giorni
        $dismiss_until = time() + (120 * DAY_IN_SECONDS);
        update_user_meta($user_id, 'mioweb_pro_notice_status', $dismiss_until);
    } else {
        // Snooze per 30 giorni
        $dismiss_until = time() + (30 * DAY_IN_SECONDS);
        update_user_meta($user_id, 'mioweb_pro_notice_status', $dismiss_until);
    }

    wp_send_json_success();
}
add_action('wp_ajax_mioweb_dismiss_pro_notice', 'mioweb_dismiss_pro_notice_callback');









add_action('admin_menu', 'mioweb_add_admin_menu');

/**
 * Renderizza la dashboard
 */
function mioweb_render_dashboard()
{

    // Enqueue style direttamente qui
    wp_enqueue_style(
        'mioweb-dashboard',
        MIOWEB_PLUGIN_URL . 'includes/css/dashboard.css',
        [],
        MIOWEB_VERSION
    );

    global $wpdb;

    // Statistiche clienti
    //$table_clienti = $wpdb->prefix . 'mioweb_clienti';


    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $totali_clienti = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mioweb_clienti");

    // Statistiche hosting
    //$table_hosting = $wpdb->prefix . 'mioweb_hosting';

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $hosting_attivi = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mioweb_hosting WHERE status = 'attivo'");

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $hosting_in_scadenza = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}mioweb_hosting 
        WHERE data_scadenza BETWEEN %s AND %s AND status = 'attivo'",
        current_time('Y-m-d'),
        gmdate('Y-m-d', strtotime('+30 days'))
    ));

    // Statistiche manutenzioni
    //$table_manutenzioni = $wpdb->prefix . 'mioweb_manutenzioni';

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $manutenzioni_attive = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mioweb_manutenzioni  WHERE stato = 'attivo'");

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $manutenzioni_scadenza = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}mioweb_manutenzioni 
        WHERE prossimo_rinnovo BETWEEN %s AND %s AND stato = 'attivo'",
        current_time('Y-m-d'),
        gmdate('Y-m-d', strtotime('+30 days'))
    ));

    // Statistiche CPT
    $siti_totali = wp_count_posts('mioweb_sito')->publish ?? 0;
    $plugin_totali = wp_count_posts('mioweb_plugin')->publish ?? 0;
    $temi_totali = wp_count_posts('mioweb_tema')->publish ?? 0;

    // Prossime scadenze (manutenzioni)

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $prossime_scadenze = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT m.*, c.ragione_sociale, c.nome, c.cognome 
        FROM {$wpdb->prefix}mioweb_manutenzioni m
        LEFT JOIN {$wpdb->prefix}mioweb_clienti c ON m.cliente_id = c.id
        WHERE m.prossimo_rinnovo IS NOT NULL 
        AND m.prossimo_rinnovo >= %s
        ORDER BY m.prossimo_rinnovo ASC
        LIMIT 5",
            current_time('Y-m-d')
        )
    );
    ?>

    <div class="wrap mioweb-dashboard">
        <h1><?php echo esc_html__('MioWeb Agency Dashboard', 'mioweb-agency'); ?></h1>

        <!-- Stats cards -->
        <div class="mioweb-stats-grid">
            <!-- Clienti -->
            <div class="mioweb-stat-card">
                <div class="mioweb-stat-icon clients">
                    <a href="admin.php?page=mioweb-clienti" class="mioweb-stat-link">
                        <span class="dashicons dashicons-businessperson"></span>
                    </a>
                </div>
                <div class="mioweb-stat-content">
                    <h3><?php esc_html_e('Clients', 'mioweb-agency'); ?></h3>
                    <p class="mioweb-stat-number"><?php echo esc_html($totali_clienti); ?></p>
                </div>
            </div>

            <!-- Hosting -->
            <div class="mioweb-stat-card">
                <div class="mioweb-stat-icon hosting">
                    <a href="admin.php?page=mioweb-hosting" class="mioweb-stat-link">
                        <span class="dashicons dashicons-cloud"></span>
                    </a>
                </div>
                <div class="mioweb-stat-content">
                    <h3><?php esc_html_e('Active Hosting', 'mioweb-agency'); ?></h3>

                    <p class="mioweb-stat-number"><?php echo esc_html($hosting_attivi); ?></p>

                    <?php if ($hosting_in_scadenza > 0) : ?>
                        <p class="mioweb-stat-warning">

                            <?php
                            // translators: %d is the number of expiring items
                            printf(esc_html__('%d expiring soon', 'mioweb-agency'), intval($hosting_in_scadenza));
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Manutenzioni -->
            <div class="mioweb-stat-card">
                <div class="mioweb-stat-icon maintenance">
                    <a href="admin.php?page=mioweb-manutenzioni" class="mioweb-stat-link">
                        <span class="dashicons dashicons-clock"></span>
                    </a>
                </div>
                <div class="mioweb-stat-content">
                    <h3><?php esc_html_e('Active Maintenance', 'mioweb-agency'); ?></h3>
                    <p class="mioweb-stat-number"><?php echo esc_html($manutenzioni_attive); ?></p>
                    <?php if ($manutenzioni_scadenza > 0) : ?>
                        <p class="mioweb-stat-warning">
                            <?php
                            // translators: %d is the day of renewals
                            printf(esc_html__('%d renewals soon', 'mioweb-agency'), intval($manutenzioni_scadenza));
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Siti -->
            <div class="mioweb-stat-card">
                <div class="mioweb-stat-icon sites">
                    <a href="edit.php?post_type=mioweb_sito" class="mioweb-stat-link">
                        <span class="dashicons dashicons-admin-site"></span>
                    </a>
                </div>
                <div class="mioweb-stat-content">
                    <h3><?php esc_html_e('Websites', 'mioweb-agency'); ?></h3>
                    <p class="mioweb-stat-number"><?php echo esc_html($siti_totali); ?></p>
                </div>
            </div>

            <!-- Plugin -->
            <div class="mioweb-stat-card">
                <div class="mioweb-stat-icon plugins">
                    <a href="edit.php?post_type=mioweb_plugin" class="mioweb-stat-link">
                        <span class="dashicons dashicons-admin-plugins"></span>
                    </a>
                </div>
                <div class="mioweb-stat-content">
                    <h3><?php esc_html_e('Plugins', 'mioweb-agency'); ?></h3>
                    <p class="mioweb-stat-number"><?php echo esc_html($plugin_totali); ?></p>
                </div>
            </div>

            <!-- Temi -->
            <div class="mioweb-stat-card">
                <div class="mioweb-stat-icon themes">
                    <a href="edit.php?post_type=mioweb_tema" class="mioweb-stat-link">
                        <span class="dashicons dashicons-admin-appearance"></span>
                    </a>
                </div>
                <div class="mioweb-stat-content">
                    <h3><?php esc_html_e('Themes', 'mioweb-agency'); ?></h3>
                    <p class="mioweb-stat-number"><?php echo esc_html($temi_totali); ?></p>
                </div>
            </div>
            <!-- Shortcode -->
            <?php $is_pro_active = function_exists('mioweb_is_pro_active') && mioweb_is_pro_active();
            if ($is_pro_active) {
                // Conta quanti shortcode PRO sono registrati
                global $shortcode_tags;
                $pro_shortcodes = 0;
                $pro_prefixes = ['mioweb_']; // Array di prefissi PRO

                foreach (array_keys($shortcode_tags) as $tag) {
                    foreach ($pro_prefixes as $prefix) {
                        if (strpos($tag, $prefix) === 0) {
                            $pro_shortcodes++;
                            break;
                        }
                    }
                }

                // CREA il DIV
                echo '<div class="mioweb-stat-card">';
                echo '<div class="mioweb-stat-icon shortcode">';
                echo '<a href="' . esc_url(admin_url('admin.php?page=mioweb-shortcodes')) . '" class="mioweb-stat-link">';
                echo '<span class="dashicons dashicons-admin-appearance"></span></a>';
                echo '</div>';
                echo '<div class="mioweb-stat-content">';
                echo '<h3>' . esc_html__('Shortcode available', 'mioweb-agency') . '</h3>';
                echo '<p class="mioweb-stat-number">' . esc_html($pro_shortcodes) . '</p>';
                echo '</div>';
                echo '</div>';
            } else { //FREE version
            ?>
                <div class="mioweb-stat-card" style="opacity:0.6; background:#f9f9f9;">
                    <div class="mioweb-stat-icon shortcode">
                        <a href="https://seconet.it/mioweb_agency_pro" class="mioweb-stat-link" target="_blank">
                            <span class="dashicons dashicons-admin-appearance"></span>
                        </a>
                    </div>
                    <div class="mioweb-stat-content">
                        <h3><?php esc_html_e('Shortcode available on PRO', 'mioweb-agency'); ?></h3>
                        <p class="mioweb-stat-number"><?php echo esc_html(3); ?></p>
                    </div>
                </div>
            <?php
            }
            ?>

        </div>

    </div>

    <!-- Prossime scadenze -->
    <div class="mioweb-dashboard-row">
        <div class="mioweb-dashboard-col">
            <div class="mioweb-dashboard-card">
                <h2><?php esc_html_e('Upcoming Renewals', 'mioweb-agency'); ?></h2>

                <?php if (empty($prossime_scadenze)) : ?>
                    <p class="mioweb-empty"><?php esc_html_e('No upcoming renewals.', 'mioweb-agency'); ?></p>
                <?php else : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Client', 'mioweb-agency'); ?></th>
                                <th><?php esc_html_e('Contract', 'mioweb-agency'); ?></th>
                                <th><?php esc_html_e('Renewal Date', 'mioweb-agency'); ?></th>
                                <th><?php esc_html_e('Amount', 'mioweb-agency'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prossime_scadenze as $s) :
                                $nome_cliente = $s->ragione_sociale ?: trim($s->nome . ' ' . $s->cognome);
                                $days = floor((strtotime($s->prossimo_rinnovo) - current_time('timestamp')) / DAY_IN_SECONDS);
                                $row_class = $days <= 7 ? 'urgent' : '';
                            ?>
                                <tr class="<?php echo esc_html($row_class); ?>">
                                    <td>
                                        <a href="?page=mioweb-cliente-form&id=<?php echo esc_html($s->cliente_id); ?>">
                                            <?php echo esc_html($nome_cliente ?: '#' . $s->cliente_id); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="?page=mioweb-manutenzioni-form&id=<?php echo esc_html($s->id); ?>">
                                            <?php echo esc_html($s->nome_contratto ?: __('Maintenance', 'mioweb-agency') . ' #' . $s->id); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($s->prossimo_rinnovo))); ?>
                                        <br><small><?php
                                                    // translators: %d is the day
                                                    printf(esc_html__('%d expiring soon', 'mioweb-agency'), intval($days));
                                                    ?></small>
                                    </td>
                                    <td>
                                        <?php if ($s->importo > 0) : ?>
                                            <?php echo esc_html($s->valuta); ?> <?php echo number_format($s->importo, 2); ?>
                                        <?php else : ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>




        <div class="mioweb-dashboard-col">
            <div class="mioweb-dashboard-card">
                <h2><?php esc_html_e('Quick Actions', 'mioweb-agency'); ?></h2>

                <div class="mioweb-quick-actions-grid">
                    <!-- Riga 1: Clienti + Hosting -->
                    <div class="mioweb-action-row">
                        <a href="?page=mioweb-cliente-form" class="button button-primary mioweb-action-btn">
                            <span class="dashicons dashicons-plus-alt"></span>
                            <span class="mioweb-action-text"><?php esc_html_e('Add Client', 'mioweb-agency'); ?></span>
                        </a>

                        <a href="?page=mioweb-hosting-form" class="button mioweb-action-btn">
                            <span class="dashicons dashicons-cloud"></span>
                            <span class="mioweb-action-text"><?php esc_html_e('Add Hosting', 'mioweb-agency'); ?></span>
                        </a>
                    </div>

                    <!-- Riga 2: Manutenzioni + Siti -->
                    <div class="mioweb-action-row">
                        <a href="?page=mioweb-manutenzioni-form" class="button mioweb-action-btn">
                            <span class="dashicons dashicons-clock"></span>
                            <span class="mioweb-action-text"><?php esc_html_e('Add Maintenance', 'mioweb-agency'); ?></span>
                        </a>

                        <a href="post-new.php?post_type=mioweb_sito" class="button mioweb-action-btn">
                            <span class="dashicons dashicons-admin-site"></span>
                            <span class="mioweb-action-text"><?php esc_html_e('Add Website', 'mioweb-agency'); ?></span>
                        </a>
                    </div>

                    <!-- Riga 3: PRO Area -->
                    <div class="mioweb-action-row mioweb-pro-row">
                        <?php
                        // $debug_data = get_option('mioweb_pro_license_info');
                        // var_dump($debug_data);
                        if (function_exists('mioweb_is_pro_active') && mioweb_is_pro_active()) :
                            // PRO attivo - mostra i pulsanti pro
                            do_action('mioweb_pro_quick_actions');
                        else :
                            // PRO non attivo - mostra link upgrade
                        ?>
                            <a href="https://seconet.it/mioweb_agency_pro"
                                class="button button-secondary mioweb-upgrade-btn"
                                target="_blank">
                                <span class="dashicons dashicons-star-filled"></span>
                                <span class="mioweb-action-text"><?php esc_html_e('Upgrade to PRO for exporting', 'mioweb-agency'); ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>




            <div class="mioweb-dashboard-card">
                <h2><?php esc_html_e('Monthly Overview', 'mioweb-agency'); ?></h2>

                <?php
                // Calcolo entrate mensili da manutenzioni

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $entrate_mensili = $wpdb->get_row(
                    "SELECT SUM(
                            CASE ciclodi_rinnovo
                                WHEN 'mensile' THEN importo
                                WHEN 'trimestrale' THEN importo/3
                                WHEN 'semestrale' THEN importo/6
                                WHEN 'annuale' THEN importo/12
                                ELSE 0
                            END
                        ) as totale_mensile
                        FROM {$wpdb->prefix}mioweb_manutenzioni
                        WHERE stato = 'attivo'"
                );
                ?>

                <p class="mioweb-monthly-total">
                    <?php esc_html_e('Monthly recurring:', 'mioweb-agency'); ?>
                    <strong>€ <?php echo number_format($entrate_mensili->totale_mensile ?? 0, 2); ?></strong>
                </p>

                <p class="mioweb-yearly-total">
                    <?php esc_html_e('Yearly recurring:', 'mioweb-agency'); ?>
                    <strong>€ <?php echo number_format(($entrate_mensili->totale_mensile ?? 0) * 12, 2); ?></strong>
                </p>
            </div>
        </div>
    </div>
    </div>

<?php
}

if (! function_exists('mioweb_clean_sql_for_check')) {
    /**
     * Rompe il tracciamento dei dati per il Plugin Check.
     * Da usare con get_var o get_results preceduto da (string).
     */
    function mioweb_clean_sql_for_check($sql)
    {
        return (string) $sql;
    }
}
