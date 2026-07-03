<?php
/**
 * Plugin Name:       MF Downloadable Gallery
 * Plugin URI:        https://mfdsgn.com/
 * Description:       Kapak görselli, kategori bazlı, e-posta ile kayıt (lead) toplayan indirilebilir dosya galerisi. Kısa kodlarla çalışır, dış bağımlılığı yoktur.
 * Version:           1.0.0
 * Author:            mfdsgn
 * Author URI:        https://mfdsgn.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mf-downloadable-gallery
 * Domain Path:       /languages
 * Requires at least: 5.6
 * Requires PHP:      7.2
 *
 * @package MF_Downloadable_Gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'MFDG_VERSION', '1.0.0' );
define( 'MFDG_FILE', __FILE__ );
define( 'MFDG_PATH', plugin_dir_path( __FILE__ ) );
define( 'MFDG_URL', plugin_dir_url( __FILE__ ) );
define( 'MFDG_BASENAME', plugin_basename( __FILE__ ) );

require_once MFDG_PATH . 'includes/class-mfdg-cpt.php';
require_once MFDG_PATH . 'includes/class-mfdg-leads.php';
require_once MFDG_PATH . 'includes/class-mfdg-settings.php';
require_once MFDG_PATH . 'includes/class-mfdg-metabox.php';
require_once MFDG_PATH . 'includes/class-mfdg-admin.php';
require_once MFDG_PATH . 'includes/class-mfdg-frontend.php';
require_once MFDG_PATH . 'includes/class-mfdg-ajax.php';
require_once MFDG_PATH . 'includes/class-mfdg-plugin.php';

/**
 * Activation: register post types (for rewrite), create leads table, flush rewrite rules.
 */
function mfdg_activate() {
	MFDG_CPT::register();
	MFDG_Leads::create_table();
	MFDG_Settings::set_defaults();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'mfdg_activate' );

/**
 * Deactivation: flush rewrite rules.
 */
function mfdg_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'mfdg_deactivate' );

/**
 * Boot the plugin.
 */
function mfdg() {
	return MFDG_Plugin::instance();
}
add_action( 'plugins_loaded', 'mfdg' );
