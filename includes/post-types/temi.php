<?php
/**
 * Tema Custom Post Type
 *
 * @package MioWebAgency
 */

// Evita accesso diretto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registra il CPT Tema
 */
function mioweb_register_tema_cpt() {
    
    $labels = array(
        'name'                  => _x( 'Themes', 'Post type general name', 'mioweb-agency' ),
        'singular_name'         => _x( 'Theme', 'Post type singular name', 'mioweb-agency' ),
        'menu_name'            => _x( 'Themes', 'Admin Menu text', 'mioweb-agency' ),
        'add_new'             => __( 'Add New Theme', 'mioweb-agency' ),
        'add_new_item'        => __( 'Add New Theme', 'mioweb-agency' ),
        'edit_item'           => __( 'Edit Theme', 'mioweb-agency' ),
        'new_item'            => __( 'New Theme', 'mioweb-agency' ),
        'view_item'           => __( 'View Theme', 'mioweb-agency' ),
        'search_items'        => __( 'Search Themes', 'mioweb-agency' ),
        'not_found'           => __( 'No themes found.', 'mioweb-agency' ),
        'not_found_in_trash'  => __( 'No themes found in Trash.', 'mioweb-agency' ),
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
        'menu_icon'         => 'dashicons-admin-appearance',
        'supports'          => array( 'title', 'editor', 'thumbnail' ),
        'show_in_rest'      => false,
    );

    register_post_type( 'mioweb_tema', $args );
    
    flush_rewrite_rules();
}
add_action( 'init', 'mioweb_register_tema_cpt' );

/**
 * Metabox: Dettagli Tema
 */
function mioweb_tema_metabox() {
    add_meta_box(
        'mioweb_tema_details',
        __( 'Theme Details', 'mioweb-agency' ),
        'mioweb_tema_details_html',
        'mioweb_tema',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'mioweb_tema_metabox' );

/**
 * HTML del metabox
 */
function mioweb_tema_details_html( $post ) {
    wp_nonce_field( 'mioweb_tema_save', 'mioweb_tema_nonce' );
    
    $versione = get_post_meta( $post->ID, '_tema_versione', true );
    $wp_versione_min = get_post_meta( $post->ID, '_tema_wp_min', true );
    $php_versione_min = get_post_meta( $post->ID, '_tema_php_min', true );
    $repo_url = get_post_meta( $post->ID, '_tema_repo_url', true );
    $sito_web = get_post_meta( $post->ID, '_tema_sito_web', true );
    $framework = get_post_meta( $post->ID, '_tema_framework', true );
    $parent_theme = get_post_meta( $post->ID, '_tema_parent', true );
    $attivo = get_post_meta( $post->ID, '_tema_attivo', true );
    $installazioni = get_post_meta( $post->ID, '_tema_installazioni', true );
    $licenza = get_post_meta( $post->ID, '_tema_licenza', true );
    $tags = get_post_meta( $post->ID, '_tema_tags', true );
    ?>
    
    <table class="form-table">
        <tr>
            <th><label for="tema_versione"><?php esc_html_e( 'Current Version', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="text" 
                       id="tema_versione" 
                       name="tema_versione" 
                       value="<?php echo esc_attr( $versione ); ?>" 
                       class="regular-text"
                       placeholder="1.0.0">
            </td>
        </tr>
        
        <tr>
            <th><label for="tema_wp_min"><?php esc_html_e( 'WordPress Min Version', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="text" 
                       id="tema_wp_min" 
                       name="tema_wp_min" 
                       value="<?php echo esc_attr( $wp_versione_min ); ?>" 
                       class="regular-text"
                       placeholder="5.0">
            </td>
        </tr>
        
        <tr>
            <th><label for="tema_php_min"><?php esc_html_e( 'PHP Min Version', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="text" 
                       id="tema_php_min" 
                       name="tema_php_min" 
                       value="<?php echo esc_attr( $php_versione_min ); ?>" 
                       class="regular-text"
                       placeholder="7.4">
            </td>
        </tr>
        
        <tr>
            <th><label for="tema_framework"><?php esc_html_e( 'Framework', 'mioweb-agency' ); ?></label></th>
            <td>
                <select id="tema_framework" name="tema_framework">
                    <option value=""><?php esc_html_e( 'None', 'mioweb-agency' ); ?></option>
                    <option value="bootstrap" <?php selected( $framework, 'bootstrap' ); ?>>Bootstrap</option>
                    <option value="tailwind" <?php selected( $framework, 'tailwind' ); ?>>Tailwind</option>
                    <option value="foundation" <?php selected( $framework, 'foundation' ); ?>>Foundation</option>
                    <option value="underscores" <?php selected( $framework, 'underscores' ); ?>>Underscores</option>
                    <option value="genesis" <?php selected( $framework, 'genesis' ); ?>>Genesis</option>
                    <option value="custom" <?php selected( $framework, 'custom' ); ?>><?php esc_html_e( 'Custom', 'mioweb-agency' ); ?></option>
                </select>
            </td>
        </tr>
        
        <tr>
            <th><label for="tema_parent"><?php esc_html_e( 'Parent Theme', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="text" 
                       id="tema_parent" 
                       name="tema_parent" 
                       value="<?php echo esc_attr( $parent_theme ); ?>" 
                       class="regular-text"
                       placeholder="twenty-twenty-four">
                <p class="description"><?php esc_html_e( 'Leave empty if it\'s a standalone theme', 'mioweb-agency' ); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="tema_repo_url"><?php esc_html_e( 'Repository URL', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="url" 
                       id="tema_repo_url" 
                       name="tema_repo_url" 
                       value="<?php echo esc_attr( $repo_url ); ?>" 
                       class="regular-text"
                       placeholder="https://github.com/username/theme">
                <p class="description"><?php esc_html_e( 'GitHub, WordPress.org, etc.', 'mioweb-agency' ); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="tema_sito_web"><?php esc_html_e( 'Demo URL', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="url" 
                       id="tema_sito_web" 
                       name="tema_sito_web" 
                       value="<?php echo esc_attr( $sito_web ); ?>" 
                       class="regular-text"
                       placeholder="https://demo.theme.com">
            </td>
        </tr>
        
        <tr>
            <th><label for="tema_installazioni"><?php esc_html_e( 'Active Installations', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="number" 
                       id="tema_installazioni" 
                       name="tema_installazioni" 
                       value="<?php echo esc_attr( $installazioni ); ?>" 
                       class="regular-text"
                       placeholder="1000">
                <p class="description"><?php esc_html_e( 'Approximate number', 'mioweb-agency' ); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="tema_licenza"><?php esc_html_e( 'License', 'mioweb-agency' ); ?></label></th>
            <td>
                <select id="tema_licenza" name="tema_licenza">
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
            <th><label for="tema_tags"><?php esc_html_e( 'Tags', 'mioweb-agency' ); ?></label></th>
            <td>
                <input type="text" 
                       id="tema_tags" 
                       name="tema_tags" 
                       value="<?php echo esc_attr( $tags ); ?>" 
                       class="regular-text"
                       placeholder="responsive, ecommerce, blog">
                <p class="description"><?php esc_html_e( 'Comma separated', 'mioweb-agency' ); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="tema_attivo"><?php esc_html_e( 'On WordPress.org', 'mioweb-agency' ); ?></label></th>
            <td>
                <label>
                    <input type="checkbox" 
                           id="tema_attivo" 
                           name="tema_attivo" 
                           value="1" 
                           <?php checked( $attivo, '1' ); ?>>
                    <?php esc_html_e( 'Published on WordPress.org repository', 'mioweb-agency' ); ?>
                </label>
            </td>
        </tr>
    </table>
    
    <p class="description">
        <?php esc_html_e( 'Upload a theme screenshot using the Featured Image box on the right (recommended 1200x900).', 'mioweb-agency' ); ?>
    </p>
    <?php
}

/**
 * Salva i dati
 */
function mioweb_tema_save_meta( $post_id, $post ) {
    if ( ! isset( $_POST['mioweb_tema_nonce'] ) ) return;
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['mioweb_tema_nonce'], 'mioweb_tema_save' )) ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( $post->post_type !== 'mioweb_tema' ) return;
    
    $fields = [
        'tema_versione' => '_tema_versione',
        'tema_wp_min' => '_tema_wp_min',
        'tema_php_min' => '_tema_php_min',
        'tema_framework' => '_tema_framework',
        'tema_parent' => '_tema_parent',
        'tema_repo_url' => '_tema_repo_url',
        'tema_sito_web' => '_tema_sito_web',
        'tema_installazioni' => '_tema_installazioni',
        'tema_licenza' => '_tema_licenza',
        'tema_tags' => '_tema_tags',
        'tema_attivo' => '_tema_attivo'
    ];
    
    foreach ( $fields as $field => $meta_key ) {
        if ( isset( $_POST[ $field ] ) ) {
            $value = sanitize_text_field( wp_unslash($_POST[ $field ]) );
            update_post_meta( $post_id, $meta_key, $value );
        } else {
            // Per checkbox non spuntati
            if ( $field === 'tema_attivo' ) {
                update_post_meta( $post_id, $meta_key, '0' );
            }
        }
    }
}
add_action( 'save_post', 'mioweb_tema_save_meta', 10, 2 );

/**
 * Colonne personalizzate
 */
function mioweb_tema_columns( $columns ) {
    $new_columns = [];
    $new_columns['cb'] = $columns['cb'];
    $new_columns['thumbnail'] = __( 'Screenshot', 'mioweb-agency' );
    $new_columns['title'] = __( 'Theme Name', 'mioweb-agency' );
    $new_columns['versione'] = __( 'Version', 'mioweb-agency' );
    $new_columns['framework'] = __( 'Framework', 'mioweb-agency' );
    $new_columns['parent'] = __( 'Parent', 'mioweb-agency' );
    $new_columns['repo'] = __( 'Repository', 'mioweb-agency' );
    $new_columns['attivo'] = __( 'On WP.org', 'mioweb-agency' );
    $new_columns['date'] = $columns['date'];
    return $new_columns;
}
add_filter( 'manage_mioweb_tema_posts_columns', 'mioweb_tema_columns' );

/**
 * Contenuto colonne
 */
function mioweb_tema_columns_content( $column, $post_id ) {
    switch ( $column ) {
        case 'thumbnail':
            if ( has_post_thumbnail( $post_id ) ) {
                echo get_the_post_thumbnail( $post_id, array(60, 45) );
            }
            break;
            
        case 'versione':
            echo esc_html( get_post_meta( $post_id, '_tema_versione', true ) ?: '—' );
            break;
            
        case 'framework':
            $framework = get_post_meta( $post_id, '_tema_framework', true );
            $frameworks = [
                'bootstrap' => 'Bootstrap',
                'tailwind' => 'Tailwind',
                'foundation' => 'Foundation',
                'underscores' => 'Underscores',
                'genesis' => 'Genesis',
                'custom' => __( 'Custom', 'mioweb-agency' )
            ];
            echo isset( $frameworks[ $framework ] ) ? esc_html( $frameworks[ $framework ] ) : '—';
            break;
            
        case 'parent':
            $parent = get_post_meta( $post_id, '_tema_parent', true );
            echo esc_html( $parent ?: '—' );
            break;
            
        case 'repo':
            $repo = get_post_meta( $post_id, '_tema_repo_url', true );
            if ( $repo ) {
                $host = wp_parse_url( $repo, PHP_URL_HOST );
                echo '<a href="' . esc_url( $repo ) . '" target="_blank">' . esc_html( $host ?: 'repo' ) . '</a>';
            } else {
                echo '—';
            }
            break;
            
        case 'attivo':
            $attivo = get_post_meta( $post_id, '_tema_attivo', true );
            if ( $attivo == '1' ) {
                echo '<span style="color:#00a32a;">✓ ' . esc_html__( 'Yes', 'mioweb-agency' ) . '</span>';
            } else {
                echo '<span style="color:#999;">✗ ' . esc_html__( 'No', 'mioweb-agency' ) . '</span>';
            }
            break;
    }
}
add_action( 'manage_mioweb_tema_posts_custom_column', 'mioweb_tema_columns_content', 10, 2 );

/**
 * CSS per la lista
 */
function mioweb_tema_admin_css() {
    $screen = get_current_screen();
    if ( $screen->post_type !== 'mioweb_tema' ) return;
    ?>
    <style>
        .column-thumbnail {
            width: 70px;
        }
        .column-thumbnail img {
            width: 60px;
            height: 45px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .column-versione {
            width: 80px;
        }
        .column-framework {
            width: 100px;
        }
        .column-parent {
            width: 100px;
        }
        .column-attivo {
            width: 80px;
        }
    </style>
    <?php
}
add_action( 'admin_head', 'mioweb_tema_admin_css' );