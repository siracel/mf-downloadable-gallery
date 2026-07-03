<?php
/**
 * Plugin settings.
 *
 * @package MF_Downloadable_Gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MFDG_Settings
 */
class MFDG_Settings {

	const OPTION = 'mfdg_settings';

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'default_require_email' => 1,
			'consent_enabled'       => 1,
			'consent_text'          => __( 'I agree that my e-mail address may be stored so this file can be sent to me and to receive information.', 'mf-downloadable-gallery' ),
			'button_label'          => __( 'Download', 'mf-downloadable-gallery' ),
			'gated_button_label'    => __( 'Get the file', 'mf-downloadable-gallery' ),
			'success_message'       => __( 'Thank you! Your download is starting.', 'mf-downloadable-gallery' ),
			'collect_name'          => 0,
			'remember_visitor'      => 1,
			'notify_admin'          => 0,
			'notify_email'          => get_option( 'admin_email' ),
			'columns'               => 3,
		);
	}

	/**
	 * Store defaults on activation (without overwriting existing).
	 */
	public static function set_defaults() {
		$existing = get_option( self::OPTION, array() );
		if ( empty( $existing ) ) {
			update_option( self::OPTION, self::defaults() );
		}
	}

	/**
	 * Get all settings merged with defaults.
	 *
	 * @return array
	 */
	public static function all() {
		$saved = get_option( self::OPTION, array() );
		return wp_parse_args( $saved, self::defaults() );
	}

	/**
	 * Get a single setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Fallback.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$all = self::all();
		if ( isset( $all[ $key ] ) ) {
			return $all[ $key ];
		}
		return $default;
	}

	/**
	 * Save settings from a raw array (sanitized).
	 *
	 * @param array $input Raw input.
	 */
	public static function save( $input ) {
		$clean = array(
			'default_require_email' => empty( $input['default_require_email'] ) ? 0 : 1,
			'consent_enabled'       => empty( $input['consent_enabled'] ) ? 0 : 1,
			'consent_text'          => isset( $input['consent_text'] ) ? wp_kses_post( $input['consent_text'] ) : '',
			'button_label'          => isset( $input['button_label'] ) ? sanitize_text_field( $input['button_label'] ) : '',
			'gated_button_label'    => isset( $input['gated_button_label'] ) ? sanitize_text_field( $input['gated_button_label'] ) : '',
			'success_message'       => isset( $input['success_message'] ) ? sanitize_text_field( $input['success_message'] ) : '',
			'collect_name'          => empty( $input['collect_name'] ) ? 0 : 1,
			'remember_visitor'      => empty( $input['remember_visitor'] ) ? 0 : 1,
			'notify_admin'          => empty( $input['notify_admin'] ) ? 0 : 1,
			'notify_email'          => isset( $input['notify_email'] ) ? sanitize_email( $input['notify_email'] ) : '',
			'columns'               => isset( $input['columns'] ) ? max( 1, min( 6, absint( $input['columns'] ) ) ) : 3,
		);

		update_option( self::OPTION, $clean );
	}
}
