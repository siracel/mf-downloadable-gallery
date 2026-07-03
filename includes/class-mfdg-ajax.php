<?php
/**
 * AJAX handlers.
 *
 * @package MF_Downloadable_Gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MFDG_Ajax
 */
class MFDG_Ajax {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_mfdg_submit_lead', array( $this, 'submit_lead' ) );
		add_action( 'wp_ajax_nopriv_mfdg_submit_lead', array( $this, 'submit_lead' ) );

		add_action( 'wp_ajax_mfdg_filter', array( $this, 'filter' ) );
		add_action( 'wp_ajax_nopriv_mfdg_filter', array( $this, 'filter' ) );

		add_action( 'wp_ajax_mfdg_track', array( $this, 'track' ) );
		add_action( 'wp_ajax_nopriv_mfdg_track', array( $this, 'track' ) );
	}

	/**
	 * Verify nonce for public actions.
	 */
	private function verify() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'mfdg_front' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page.', 'mf-downloadable-gallery' ) ), 403 );
		}
	}

	/**
	 * Handle a gated download lead submission.
	 */
	public function submit_lead() {
		$this->verify();

		$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$file_id = isset( $_POST['file_id'] ) ? absint( $_POST['file_id'] ) : 0;
		$consent = ! empty( $_POST['consent'] ) ? 1 : 0;

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid e-mail address.', 'mf-downloadable-gallery' ) ) );
		}

		$post = $file_id ? get_post( $file_id ) : null;
		if ( ! $post || MFDG_CPT::POST_TYPE !== $post->post_type ) {
			wp_send_json_error( array( 'message' => __( 'File not found.', 'mf-downloadable-gallery' ) ) );
		}

		$settings = MFDG_Settings::all();
		if ( $settings['consent_enabled'] && ! $consent ) {
			wp_send_json_error( array( 'message' => __( 'Please accept the consent to continue.', 'mf-downloadable-gallery' ) ) );
		}

		$attachment_id = (int) get_post_meta( $file_id, '_mfdg_file_id', true );
		$file_url      = $attachment_id ? wp_get_attachment_url( $attachment_id ) : '';
		if ( ! $file_url ) {
			wp_send_json_error( array( 'message' => __( 'The file is not available.', 'mf-downloadable-gallery' ) ) );
		}

		$terms     = get_the_terms( $file_id, MFDG_CPT::TAXONOMY );
		$cat_names = ( $terms && ! is_wp_error( $terms ) ) ? wp_list_pluck( $terms, 'name' ) : array();

		MFDG_Leads::insert(
			array(
				'email'      => $email,
				'name'       => $name,
				'file_id'    => $file_id,
				'file_title' => get_the_title( $file_id ),
				'category'   => implode( ', ', $cat_names ),
				'consent'    => $consent,
				'ip'         => $this->get_ip(),
			)
		);

		$this->increment_downloads( $file_id );
		$this->maybe_notify( $settings, $email, get_the_title( $file_id ) );

		wp_send_json_success(
			array(
				'file_url' => $file_url,
				'filename' => sanitize_file_name( get_the_title( $file_id ) ),
				'message'  => $settings['success_message'],
			)
		);
	}

	/**
	 * AJAX category filter — returns the rendered grid HTML.
	 */
	public function filter() {
		$this->verify();

		$category = isset( $_POST['category'] ) ? sanitize_title( wp_unslash( $_POST['category'] ) ) : '';
		$columns  = isset( $_POST['columns'] ) ? absint( $_POST['columns'] ) : MFDG_Settings::get( 'columns', 3 );
		$orderby  = isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : 'date';
		$order    = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : 'DESC';
		$limit    = isset( $_POST['limit'] ) ? (int) $_POST['limit'] : -1;

		$atts = array(
			'columns' => $columns,
			'orderby' => $orderby,
			'order'   => $order,
			'limit'   => $limit,
			'ids'     => '',
		);

		$frontend = new MFDG_Frontend();
		$html     = $frontend->render_items( $atts, $category );

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Track a direct (non-gated) download count.
	 */
	public function track() {
		$this->verify();
		$file_id = isset( $_POST['file_id'] ) ? absint( $_POST['file_id'] ) : 0;
		if ( $file_id && MFDG_CPT::POST_TYPE === get_post_type( $file_id ) ) {
			$this->increment_downloads( $file_id );
		}
		wp_send_json_success();
	}

	/**
	 * Increment the per-file download counter.
	 *
	 * @param int $file_id Post id.
	 */
	private function increment_downloads( $file_id ) {
		$count = (int) get_post_meta( $file_id, '_mfdg_downloads', true );
		update_post_meta( $file_id, '_mfdg_downloads', $count + 1 );
	}

	/**
	 * Optionally notify the admin of a new lead.
	 *
	 * @param array  $settings Settings.
	 * @param string $email    Lead e-mail.
	 * @param string $file     File title.
	 */
	private function maybe_notify( $settings, $email, $file ) {
		if ( empty( $settings['notify_admin'] ) ) {
			return;
		}
		$to = ! empty( $settings['notify_email'] ) ? $settings['notify_email'] : get_option( 'admin_email' );
		if ( ! is_email( $to ) ) {
			return;
		}
		$subject = sprintf(
			/* translators: %s: site name */
			__( '[%s] New download lead', 'mf-downloadable-gallery' ),
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
		);
		$body = sprintf(
			/* translators: 1: e-mail, 2: file title */
			__( "A new e-mail was captured.\n\nE-mail: %1\$s\nFile: %2\$s", 'mf-downloadable-gallery' ),
			$email,
			$file
		);
		wp_mail( $to, $subject, $body );
	}

	/**
	 * Best-effort visitor IP.
	 *
	 * @return string
	 */
	private function get_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return $ip;
	}
}
