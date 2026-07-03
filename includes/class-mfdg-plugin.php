<?php
/**
 * Main plugin orchestrator.
 *
 * @package MF_Downloadable_Gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MFDG_Plugin
 */
final class MFDG_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var MFDG_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return MFDG_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor: wire up components.
	 */
	private function __construct() {
		add_action( 'init', array( 'MFDG_CPT', 'register' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );

		new MFDG_Metabox();
		new MFDG_Admin();
		new MFDG_Frontend();
		new MFDG_Ajax();
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'mf-downloadable-gallery',
			false,
			dirname( MFDG_BASENAME ) . '/languages'
		);
	}
}
