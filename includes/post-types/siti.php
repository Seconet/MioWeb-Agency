<?php
/**
 * Sito Custom Post Type
 *
 * @package MioWebAgency
 */

// Evita accesso diretto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registra il CPT Sito
 */
function mioweb_register_sito_cpt() {
    
    $labels = array(
        'name'                  => _x( 'Websites', 'Post type general name', 'mioweb-agency' ),
        'singular_name'         => _x( 'Website', 'Post type singular name', 'mioweb-agency' ),
        'menu_name'            => _x( 'Websites', 'Admin Menu text', 'mioweb-agency' ),
        'add_new'             => __( 'Add New Website', 'mioweb-agency' ),
        'add_new_item'        => __( 'Add New Website', 'mioweb-agency' ),
        'edit_item'           => __( 'Edit Website', 'mioweb-agency' ),
        'new_item'            => __( 'New Website', 'mioweb-agency' ),
        'view_item'           => __( 'View Website', 'mioweb-agency' ),
        'search_items'        => __( 'Search Websites', 'mioweb-agency' ),
        'not_found'           => __( 'No websites found.', 'mioweb-agency' ),
        'not_found_in_trash'  => __( 'No websites found in Trash.', 'mioweb-agency' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => false,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_icon'         => 'dashicons-admin-site',
        'supports'          => array( 'title', 'thumbnail' ), // solo titolo e miniatura
        'show_in_rest'      => false,
    );

    register_post_type( 'mioweb_sito', $args );
    
    // Forza il flush delle rewrite rules
    flush_rewrite_rules();
}
add_action( 'init', 'mioweb_register_sito_cpt' );

/**
 * Metabox semplice: Dettagli Sito
 */
function mioweb_sito_metabox() {
    add_meta_box(
        'mioweb_sito_details',
        __( 'Website Details', 'mioweb-agency' ),
        'mioweb_sito_details_html',
        'mioweb_sito',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'mioweb_sito_metabox' );

/**
 * HTML del metabox
 */
function mioweb_sito_details_html( $post ) {
    wp_nonce_field( 'mioweb_sito_save', 'mioweb_sito_nonce' );
    
    $url = get_post_meta( $post->ID, '_sito_url', true );
    $cliente_id = get_post_meta( $post->ID, '_sito_cliente_id', true );
    $cms = get_post_meta( $post->ID, '_sito_cms', true );
    $stato = get_post_meta( $post->ID, '_sito_stato', true );
    
    global $wpdb;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $clienti = $wpdb->get_results(
        "SELECT id, ragione_sociale, nome, cognome 
        FROM {$wpdb->prefix}mioweb_clienti 
        ORDER BY ragione_sociale"
    );
    ?>
    
    <table class="form-table">
        <tr>
            <th><label for="sito_url"><?php esc_html_e( 'Website URL', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="url" 
                       id="sito_url" 
                       name="sito_url" 
                       value="<?php echo esc_attr( $url ); ?>" 
                       class="regular-text"
                       placeholder="https://example.com">
            </td>
        </tr>
        
        <tr>
            <th><label for="sito_cliente"><?php esc_html_e( 'Client', 'mioweb-agency' ); ?></label></th>
            <td>
                <select id="sito_cliente" name="sito_cliente">
                    <option value=""><?php esc_html_e( 'Select client', 'mioweb-agency' ); ?></option>
                    <?php foreach ( $clienti as $cliente ) : ?>
                        <option value="<?php echo esc_html( $cliente->id ); ?>" <?php selected( $cliente_id, $cliente->id ); ?>>
                            <?php 
                            echo esc_html( $cliente->ragione_sociale ?: trim( $cliente->nome . ' ' . $cliente->cognome ) );
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        
        <tr>
            <th><label for="sito_cms"><?php esc_html_e( 'CMS', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="text" 
                       id="sito_cms" 
                       name="sito_cms" 
                       value="<?php echo esc_attr( $cms ); ?>" 
                       class="regular-text"
                       placeholder="WordPress, WooCommerce, Laravel...">
            </td>
        </tr>
        
        <tr>
            <th><label for="sito_stato"><?php esc_html_e( 'Status', 'mioweb-agency' ); ?></label></th>
            <td>
                <select id="sito_stato" name="sito_stato">
                    <option value="attivo" <?php selected( $stato, 'attivo' ); ?>><?php esc_html_e( 'Active', 'mioweb-agency' ); ?></option>
                    <option value="sviluppo" <?php selected( $stato, 'sviluppo' ); ?>><?php esc_html_e( 'In Development', 'mioweb-agency' ); ?></option>
                    <option value="manutenzione" <?php selected( $stato, 'manutenzione' ); ?>><?php esc_html_e( 'Maintenance', 'mioweb-agency' ); ?></option>
                    <option value="archiviato" <?php selected( $stato, 'archiviato' ); ?>><?php esc_html_e( 'Archived', 'mioweb-agency' ); ?></option>
                </select>
            </td>
        </tr>
    </table>
    
    <p class="description">
        <?php esc_html_e( 'Upload a screenshot using the Featured Image box on the right.', 'mioweb-agency' ); ?>
    </p>
    <?php
}

/**
 * Salva i dati
 */
function mioweb_sito_save_meta( $post_id, $post ) {
    // 1. Verifica esistenza e sanitizzazione del Nonce
    $nonce = isset( $_POST['mioweb_sito_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['mioweb_sito_nonce'] ) ) : '';

    // 2. Verifica del Nonce
    if ( ! wp_verify_nonce( $nonce, 'mioweb_sito_save' ) ) return;

    // 3. Controllo permessi utente (essenziale per WP.org)
    if ( ! current_user_can( 'edit_post', $post_id ) )  return;

    // 4. Evita il salvataggio durante l'autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  return;

    // 5. Verifica del Post Type (Yoda conditions e isset)
    if ( ! isset( $post->post_type ) || 'mioweb_sito' !== $post->post_type )  return;

    // Definizione dei campi
    $fields = [
        'sito_url'     => '_sito_url',
        'sito_cliente' => '_sito_cliente_id',
        'sito_cms'     => '_sito_cms',
        'sito_stato'   => '_sito_stato'
    ];

    // 6. Ciclo di salvataggio
    foreach ( $fields as $field => $meta_key ) {
        if ( isset( $_POST[ $field ] ) ) {
            
            // Logica specifica per URL se il campo è sito_url
            if ( 'sito_url' === $field ) {
                $value = esc_url_raw( wp_unslash( $_POST[ $field ] ) );
            } else {
                $value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
            }

            update_post_meta( $post_id, $meta_key, $value );
        }
    }
}
add_action( 'save_post', 'mioweb_sito_save_meta', 10, 2 );

/**
 * Colonne personalizzate
 */
function mioweb_sito_columns( $columns ) {
    $new_columns = [];
    $new_columns['cb'] = $columns['cb'];
    $new_columns['thumbnail'] = __( 'Screenshot', 'mioweb-agency' );
    $new_columns['title'] = __( 'Site Name', 'mioweb-agency' );
    $new_columns['url'] = __( 'URL', 'mioweb-agency' );
    $new_columns['cliente'] = __( 'Client', 'mioweb-agency' );
    $new_columns['stato'] = __( 'Status', 'mioweb-agency' );
    $new_columns['date'] = $columns['date'];
    return $new_columns;
}
add_filter( 'manage_mioweb_sito_posts_columns', 'mioweb_sito_columns' );

/**
 * Contenuto colonne
 */
function mioweb_sito_columns_content( $column, $post_id ) {
    switch ( $column ) {
        case 'thumbnail':
            if ( has_post_thumbnail( $post_id ) ) {
                echo get_the_post_thumbnail( $post_id, array(60, 60) );
            }
            break;
            
        case 'url':
            $url = get_post_meta( $post_id, '_sito_url', true );
            if ( $url ) {
                echo '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( wp_parse_url($url, PHP_URL_HOST) ?: $url ) . '</a>';
            }
            break;
            
        case 'cliente':
            $cliente_id = get_post_meta( $post_id, '_sito_cliente_id', true );
            if ( $cliente_id ) {
                global $wpdb;
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $cliente = $wpdb->get_row( $wpdb->prepare(
                    "SELECT ragione_sociale, nome, cognome FROM {$wpdb->prefix}mioweb_clienti WHERE id = %d",
                    $cliente_id
                ) );
                if ( $cliente ) {
                    echo esc_html( $cliente->ragione_sociale ?: trim( $cliente->nome . ' ' . $cliente->cognome ) );
                }
            }
            break;
            
        case 'stato':
            $stato = get_post_meta( $post_id, '_sito_stato', true );
            $class = '';
            switch ( $stato ) {
                case 'attivo': $class = 'status-active'; $label = __( 'Active', 'mioweb-agency' ); break;
                case 'sviluppo': $class = 'status-development'; $label = __( 'Development', 'mioweb-agency' ); break;
                case 'manutenzione': $class = 'status-maintenance'; $label = __( 'Maintenance', 'mioweb-agency' ); break;
                case 'archiviato': $class = 'status-archived'; $label = __( 'Archived', 'mioweb-agency' ); break;
                default: $label = '—';
            }
            if ( isset($label) && $label !== '—' ) {
                echo '<span class="mioweb-status ' . esc_html( $class ) . '">' . esc_html( $label ) . '</span>';
            }
            break;
    }
}
add_action( 'manage_mioweb_sito_posts_custom_column', 'mioweb_sito_columns_content', 10, 2 );

/**
 * CSS per la lista
 */
function mioweb_sito_admin_css() {
    $screen = get_current_screen();
    if ( $screen->post_type !== 'mioweb_sito' ) return;
    ?>
    <style>
        .mioweb-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            background: #f0f0f1;
            color: #50575e;
        }
        .mioweb-status.status-active { background: #00a32a; color: white; }
        .mioweb-status.status-development { background: #2271b1; color: white; }
        .mioweb-status.status-maintenance { background: #ffb900; color: #000; }
        .mioweb-status.status-archived { background: #999; color: white; }
        .column-thumbnail { width: 70px; }
        .column-thumbnail img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
    </style>
    <?php
}
add_action( 'admin_head', 'mioweb_sito_admin_css' );