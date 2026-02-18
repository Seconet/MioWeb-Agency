<?php
/**
 * Plugin WP Custom Post Type
 *
 * @package MioWebAgency
 */

// Evita accesso diretto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registra il CPT Plugin
 */
function mioweb_register_plugin_cpt() {
    
    $labels = array(
        'name'                  => _x( 'Plugins', 'Post type general name', 'mioweb-agency' ),
        'singular_name'         => _x( 'Plugin', 'Post type singular name', 'mioweb-agency' ),
        'menu_name'            => _x( 'Plugins', 'Admin Menu text', 'mioweb-agency' ),
        'add_new'             => __( 'Add New Plugin', 'mioweb-agency' ),
        'add_new_item'        => __( 'Add New Plugin', 'mioweb-agency' ),
        'edit_item'           => __( 'Edit Plugin', 'mioweb-agency' ),
        'new_item'            => __( 'New Plugin', 'mioweb-agency' ),
        'view_item'           => __( 'View Plugin', 'mioweb-agency' ),
        'search_items'        => __( 'Search Plugins', 'mioweb-agency' ),
        'not_found'           => __( 'No plugins found.', 'mioweb-agency' ),
        'not_found_in_trash'  => __( 'No plugins found in Trash.', 'mioweb-agency' ),
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
        'menu_icon'         => 'dashicons-admin-plugins',
        'supports'          => array( 'title', 'editor', 'thumbnail' ),
        'show_in_rest'      => false,
    );

    register_post_type( 'mioweb_plugin', $args );
    
    flush_rewrite_rules();
}
add_action( 'init', 'mioweb_register_plugin_cpt' );

/**
 * Metabox: Dettagli Plugin
 */
function mioweb_plugin_metabox() {
    add_meta_box(
        'mioweb_plugin_details',
        __( 'Plugin Details', 'mioweb-agency' ),
        'mioweb_plugin_details_html',
        'mioweb_plugin',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'mioweb_plugin_metabox' );

/**
 * HTML del metabox
 */
function mioweb_plugin_details_html( $post ) {
    wp_nonce_field( 'mioweb_plugin_save', 'mioweb_plugin_nonce' );
    
    $versione = get_post_meta( $post->ID, '_plugin_versione', true );
    $wp_versione_min = get_post_meta( $post->ID, '_plugin_wp_min', true );
    $php_versione_min = get_post_meta( $post->ID, '_plugin_php_min', true );
    $repo_url = get_post_meta( $post->ID, '_plugin_repo_url', true );
    $sito_web = get_post_meta( $post->ID, '_plugin_sito_web', true );
    $attivo = get_post_meta( $post->ID, '_plugin_attivo', true );
    $installazioni = get_post_meta( $post->ID, '_plugin_installazioni', true );
    $licenza = get_post_meta( $post->ID, '_plugin_licenza', true );
    $tags = get_post_meta( $post->ID, '_plugin_tags', true );
    ?>
    
    <table class="form-table">
        <tr>
            <th><label for="plugin_versione"><?php esc_html_e( 'Current Version', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="text" 
                       id="plugin_versione" 
                       name="plugin_versione" 
                       value="<?php echo esc_attr( $versione ); ?>" 
                       class="regular-text"
                       placeholder="1.0.0">
            </td>
        </tr>
        
        <tr>
            <th><label for="plugin_wp_min"><?php esc_html_e( 'WordPress Min Version', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="text" 
                       id="plugin_wp_min" 
                       name="plugin_wp_min" 
                       value="<?php echo esc_attr( $wp_versione_min ); ?>" 
                       class="regular-text"
                       placeholder="5.0">
            </td>
        </tr>
        
        <tr>
            <th><label for="plugin_php_min"><?php esc_html_e( 'PHP Min Version', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="text" 
                       id="plugin_php_min" 
                       name="plugin_php_min" 
                       value="<?php echo esc_attr( $php_versione_min ); ?>" 
                       class="regular-text"
                       placeholder="7.4">
            </td>
        </tr>
        
        <tr>
            <th><label for="plugin_repo_url"><?php esc_html_e( 'Repository URL', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="url" 
                       id="plugin_repo_url" 
                       name="plugin_repo_url" 
                       value="<?php echo esc_attr( $repo_url ); ?>" 
                       class="regular-text"
                       placeholder="https://github.com/username/plugin">
                <p class="description"><?php esc_html_e( 'GitHub, WordPress.org, etc.', 'mioweb-agency' ); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="plugin_sito_web"><?php esc_html_e( 'Website URL', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="url" 
                       id="plugin_sito_web" 
                       name="plugin_sito_web" 
                       value="<?php echo esc_attr( $sito_web ); ?>" 
                       class="regular-text"
                       placeholder="https://plugin-site.com">
            </td>
        </tr>
        
        <tr>
            <th><label for="plugin_installazioni"><?php esc_html_e( 'Active Installations', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="number" 
                       id="plugin_installazioni" 
                       name="plugin_installazioni" 
                       value="<?php echo esc_attr( $installazioni ); ?>" 
                       class="regular-text"
                       placeholder="1000">
                <p class="description"><?php esc_html_e( 'Approximate number', 'mioweb-agency' ); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="plugin_licenza"><?php esc_html_e( 'License', 'mioweb-agency' ); ?></label></th>
            <td>
                <select id="plugin_licenza" name="plugin_licenza">
                    <option value=""><?php esc_html_e( 'Select license', 'mioweb-agency' ); ?></option>
                    <option value="gpl2" <?php selected( $licenza, 'gpl2' ); ?>>GPL v2</option>
                    <option value="gpl3" <?php selected( $licenza, 'gpl3' ); ?>>GPL v3</option>
                    <option value="mit" <?php selected( $licenza, 'mit' ); ?>>MIT</option>
                    <option value="apache2" <?php selected( $licenza, 'apache2' ); ?>>Apache 2.0</option>
                    <option value="bsd" <?php selected( $licenza, 'bsd' ); ?>>BSD</option>
                    <option value="proprietary" <?php selected( $licenza, 'proprietary' ); ?>><?php esc_html_e( 'Proprietary', 'mioweb-agency' ); ?></option>
                </select>
            </td>
        </tr>
        
        <tr>
            <th><label for="plugin_tags"><?php esc_html_e( 'Tags', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="text" 
                       id="plugin_tags" 
                       name="plugin_tags" 
                       value="<?php echo esc_attr( $tags ); ?>" 
                       class="regular-text"
                       placeholder="ecommerce, seo, security">
                <p class="description"><?php esc_html_e( 'Comma separated', 'mioweb-agency' ); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="plugin_attivo"><?php esc_html_e( 'On WordPress.org', 'mioweb-agency' ); ?></label></th>
            <td>
                <label>
                    <input type="checkbox" 
                           id="plugin_attivo" 
                           name="plugin_attivo" 
                           value="1" 
                           <?php checked( $attivo, '1' ); ?>>
                    <?php esc_html_e( 'Published on WordPress.org repository', 'mioweb-agency' ); ?>
                </label>
            </td>
        </tr>
    </table>
    
    <p class="description">
        <?php esc_html_e( 'Upload a plugin icon using the Featured Image box on the right (recommended 128x128).', 'mioweb-agency' ); ?>
    </p>
    <?php
}

/**
 * Salva i dati
 */
/*function mioweb_plugin_save_meta( $post_id, $post ) {
   

    if ( ! isset( $_POST['mioweb_plugin_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['mioweb_plugin_nonce'], 'mioweb_plugin_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( $post->post_type !== 'mioweb_plugin' ) return;
    
    $fields = [
        'plugin_versione' => '_plugin_versione',
        'plugin_wp_min' => '_plugin_wp_min',
        'plugin_php_min' => '_plugin_php_min',
        'plugin_repo_url' => '_plugin_repo_url',
        'plugin_sito_web' => '_plugin_sito_web',
        'plugin_installazioni' => '_plugin_installazioni',
        'plugin_licenza' => '_plugin_licenza',
        'plugin_tags' => '_plugin_tags',
        'plugin_attivo' => '_plugin_attivo'
    ];
    
    foreach ( $fields as $field => $meta_key ) {
        if ( isset( $_POST[ $field ] ) ) {
            $value = sanitize_text_field( $_POST[ $field ] );
            update_post_meta( $post_id, $meta_key, $value );
        } else {
            // Per checkbox non spuntati
            if ( $field === 'plugin_attivo' ) {
                update_post_meta( $post_id, $meta_key, '0' );
            }
        }
    }
}*/
function mioweb_plugin_save_meta( $post_id, $post ) {
    // 1. Verifica esistenza e sanitizzazione del Nonce
    $nonce = isset( $_POST['mioweb_plugin_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['mioweb_plugin_nonce'] ) ) : '';

    // 2. Verifica del Nonce
    if ( ! wp_verify_nonce( $nonce, 'mioweb_plugin_save' ) ) return;

    // 3. Controllo permessi utente (Fondamentale per la sicurezza)
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // 4. Evita il salvataggio durante l'autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    // 5. Verifica del Post Type (usando isset per sicurezza)
    if ( ! isset( $post->post_type ) || 'mioweb_plugin' !== $post->post_type )  return;
    
    // Definizione dei campi
    $fields = [
        'plugin_versione'      => '_plugin_versione',
        'plugin_wp_min'        => '_plugin_wp_min',
        'plugin_php_min'       => '_plugin_php_min',
        'plugin_repo_url'      => '_plugin_repo_url',
        'plugin_sito_web'      => '_plugin_sito_web',
        'plugin_installazioni' => '_plugin_installazioni',
        'plugin_licenza'       => '_plugin_licenza',
        'plugin_tags'          => '_plugin_tags',
        'plugin_attivo'        => '_plugin_attivo'
    ];

    // 6. Ciclo di salvataggio con sanitizzazione esplicita
    foreach ( $fields as $field => $meta_key ) {
        if ( isset( $_POST[ $field ] ) ) {
            // Usiamo wp_unslash prima di sanitizzare (best practice WP)
            $value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
            update_post_meta( $post_id, $meta_key, $value );
        } else {
            // Gestione specifica per il checkbox 'attivo'
            if ( 'plugin_attivo' === $field ) {
                update_post_meta( $post_id, $meta_key, '0' );
            }
        }
    }
}



add_action( 'save_post', 'mioweb_plugin_save_meta', 10, 2 );

/**
 * Colonne personalizzate
 */
function mioweb_plugin_columns( $columns ) {
    $new_columns = [];
    $new_columns['cb'] = $columns['cb'];
    $new_columns['thumbnail'] = __( 'Icon', 'mioweb-agency' );
    $new_columns['title'] = __( 'Plugin Name', 'mioweb-agency' );
    $new_columns['versione'] = __( 'Version', 'mioweb-agency' );
    $new_columns['wp_min'] = __( 'WP Min', 'mioweb-agency' );
    $new_columns['repo'] = __( 'Repository', 'mioweb-agency' );
    $new_columns['installazioni'] = __( 'Installs', 'mioweb-agency' );
    $new_columns['attivo'] = __( 'On WP.org', 'mioweb-agency' );
    $new_columns['date'] = $columns['date'];
    return $new_columns;
}
add_filter( 'manage_mioweb_plugin_posts_columns', 'mioweb_plugin_columns' );

/**
 * Contenuto colonne
 */
function mioweb_plugin_columns_content( $column, $post_id ) {
    switch ( $column ) {
        case 'thumbnail':
            if ( has_post_thumbnail( $post_id ) ) {
                echo get_the_post_thumbnail( $post_id, array(40, 40) );
            }
            break;
            
        case 'versione':
            echo esc_html( get_post_meta( $post_id, '_plugin_versione', true ) ?: '—' );
            break;
            
        case 'wp_min':
            echo esc_html( get_post_meta( $post_id, '_plugin_wp_min', true ) ?: '—' );
            break;
            
        case 'repo':
            $repo = get_post_meta( $post_id, '_plugin_repo_url', true );
            if ( $repo ) {
                $host = wp_parse_url( $repo, PHP_URL_HOST );
                echo '<a href="' . esc_url( $repo ) . '" target="_blank">' . esc_html( $host ?: 'repo' ) . '</a>';
            } else {
                echo '—';
            }
            break;
            
        case 'installazioni':
            $num = get_post_meta( $post_id, '_plugin_installazioni', true );
            echo $num ? number_format( intval($num) ) : '—';
            break;
            
        case 'attivo':
            $attivo = get_post_meta( $post_id, '_plugin_attivo', true );
            if ( $attivo == '1' ) {
                echo '<span style="color:#00a32a;">✓ ' . esc_html__( 'Yes', 'mioweb-agency' ) . '</span>';
            } else {
                echo '<span style="color:#999;">✗ ' . esc_html__( 'No', 'mioweb-agency' ) . '</span>';
            }
            break;
    }
}
add_action( 'manage_mioweb_plugin_posts_custom_column', 'mioweb_plugin_columns_content', 10, 2 );

/**
 * CSS per la lista
 */
function mioweb_plugin_admin_css() {
    $screen = get_current_screen();
    if ( $screen->post_type !== 'mioweb_plugin' ) return;
    ?>
    <style>
        .column-thumbnail {
            width: 50px;
        }
        .column-thumbnail img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            border-radius: 4px;
        }
        .column-versione,
        .column-wp_min {
            width: 70px;
        }
        .column-installazioni {
            width: 90px;
        }
        .column-attivo {
            width: 80px;
        }
    </style>
    <?php
}
add_action( 'admin_head', 'mioweb_plugin_admin_css' );