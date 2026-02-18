<?php
ob_start(); //
/**
 * Plugin Name
 *
 * @package           MioWebAgency
 * @author            Sergio Cornacchione
 * @copyright         2026 Seconet
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       MioWeb Agency Web
 * Plugin URI:        https://github.com/Seconet/MioWeb-Agency
 * Description:       A complete management system for web agencies to manage clients, hosting, maintenance contracts, websites, plugins, and themes.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Sergio Cornacchione
 * Author URI:        https://seconet.it
 * Text Domain:       mioweb-agency
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt

 */

// Evita accesso diretto
if (! defined('ABSPATH')) {
    exit;
}




// Definizioni costanti
define('MIOWEB_VERSION', '1.0.0');
define('MIOWEB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MIOWEB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MIOWEB_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once MIOWEB_PLUGIN_DIR . 'includes/db/schema.php';     // tabelle

/**
 * Carica i file necessari
 */
function mioweb_load_dependencies()
{
    // Admin settings
    if (is_admin()) {
        require_once MIOWEB_PLUGIN_DIR . 'includes/admin-settings.php';

        require_once MIOWEB_PLUGIN_DIR . 'includes/db/clienti.php';    // CLIENTI
        require_once MIOWEB_PLUGIN_DIR . 'includes/admin-pages/clienti-list.php';
        require_once MIOWEB_PLUGIN_DIR . 'includes/admin-pages/clienti-form.php';

        require_once MIOWEB_PLUGIN_DIR . 'includes/db/manutenzioni.php';  // MANUTENZIONI
        require_once MIOWEB_PLUGIN_DIR . 'includes/admin-pages/manutenzioni-form.php'; 
        require_once MIOWEB_PLUGIN_DIR . 'includes/admin-pages/manutenzioni-list.php';

        require_once MIOWEB_PLUGIN_DIR . 'includes/db/hosting.php'; // HOSTING
        require_once MIOWEB_PLUGIN_DIR . 'includes/admin-pages/hosting-list.php'; 
        require_once MIOWEB_PLUGIN_DIR . 'includes/admin-pages/hosting-form.php';

        require_once MIOWEB_PLUGIN_DIR . 'includes/post-types/siti.php'; // SITI

        require_once MIOWEB_PLUGIN_DIR . 'includes/post-types/plugin-wp.php'; // PLUGIN

        require_once MIOWEB_PLUGIN_DIR . 'includes/post-types/temi.php'; // TEMI




          
    }

    // Qui carichiamo gli altri moduli...
}
add_action('plugins_loaded', 'mioweb_load_dependencies');

/**
 * Attivazione plugin
 */
register_activation_hook(__FILE__, 'mioweb_activate');
function mioweb_activate()
{ 
    // Chiama la funzione che crea le tabelle
    mioweb_create_tables();
    // Non necessario per ora
    //flush_rewrite_rules();
}

/**
 * Disattivazione plugin
 */
register_deactivation_hook(__FILE__, 'mioweb_deactivate');
function mioweb_deactivate()
{
     // Non necessario per ora
    //flush_rewrite_rules();
}

/*
* CSS e JQuery
*/
// registra jquery e lo stile durante l'inizializzazione
add_action('init', 'mioweb_register_script');
function mioweb_register_script() {
   // wp_register_script( 'custom_jquery', plugins_url('/js/custom-jquery.js', __FILE__), array('jquery'), '2.5.1' );

    wp_register_style( 'mioweb-clienti-form', plugins_url('/includes/css/clienti-form.css', __FILE__), false, '1.0.0', 'all');
    wp_register_style( 'mioweb-clienti-list', plugins_url('/includes/css/clienti-list.css', __FILE__), false, '1.0.0', 'all');
    wp_register_style( 'mioweb-manutenzioni-list', plugins_url('/includes/css/manutenzioni-list.css', __FILE__), false, '1.0.0', 'all');
    wp_register_style( 'mioweb-manutenzioni-form', plugins_url('/includes/css/manutenzioni-form.css', __FILE__), false, '1.0.0', 'all');
    wp_register_style( 'mioweb-hosting-list', plugins_url('/includes/css/hosting-list.css', __FILE__), false, '1.0.0', 'all');
    wp_register_style( 'mioweb-hosting-form', plugins_url('/includes/css/hosting-form.css', __FILE__), false, '1.0.0', 'all');
    wp_register_style( 'mioweb-dashboard', plugins_url('/includes/css/dashboard.css', __FILE__), false, '1.0.0', 'all');
}

