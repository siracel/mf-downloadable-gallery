<?php
/**
 * Meta box for the downloadable file + settings.
 *
 * @package MF_Downloadable_Gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MFDG_Metabox
 */
class MFDG_Metabox {

	const NONCE = 'mfdg_metabox_nonce';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add' ) );
		add_action( 'save_post_' . MFDG_CPT::POST_TYPE, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Register the meta box.
	 */
	public function add() {
		add_meta_box(
			'mfdg_file_box',
			__( 'Downloadable File', 'mf-downloadable-gallery' ),
			array( $this, 'render' ),
			MFDG_CPT::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Enqueue media uploader + admin script on the edit screen.
	 *
	 * @param string $hook Current admin page.
	 */
	public function assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || MFDG_CPT::POST_TYPE !== $screen->post_type ) {
			return;
		}
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'mfdg-admin', MFDG_URL . 'assets/css/mfdg-admin.css', array(), MFDG_VERSION );
		wp_enqueue_script( 'mfdg-admin', MFDG_URL . 'assets/js/mfdg-admin.js', array( 'jquery' ), MFDG_VERSION, true );
		wp_localize_script(
			'mfdg-admin',
			'mfdgAdmin',
			array(
				'chooseFile'   => __( 'Choose file', 'mf-downloadable-gallery' ),
				'useThisFile'  => __( 'Use this file', 'mf-downloadable-gallery' ),
				'removeFile'   => __( 'Remove file', 'mf-downloadable-gallery' ),
			)
		);
	}

	/**
	 * Render the meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render( $post ) {
		wp_nonce_field( 'mfdg_save_meta', self::NONCE );

		$file_id       = (int) get_post_meta( $post->ID, '_mfdg_file_id', true );
		$require_email = get_post_meta( $post->ID, '_mfdg_require_email', true );
		$default_req   = MFDG_Settings::get( 'default_require_email' );

		// If unset (new post), fall back to the global default.
		if ( '' === $require_email ) {
			$require_email = $default_req ? '1' : '0';
		}

		$file_url  = $file_id ? wp_get_attachment_url( $file_id ) : '';
		$file_name = $file_id ? basename( get_attached_file( $file_id ) ) : '';
		?>
		<div class="mfdg-field">
			<label class="mfdg-field__label"><?php esc_html_e( 'File (PDF or any document)', 'mf-downloadable-gallery' ); ?></label>
			<p class="description"><?php esc_html_e( 'The file will be stored in the Media Library.', 'mf-downloadable-gallery' ); ?></p>

			<div class="mfdg-file-picker">
				<input type="hidden" id="mfdg_file_id" name="mfdg_file_id" value="<?php echo esc_attr( $file_id ); ?>" />
				<button type="button" class="button mfdg-select-file"><?php esc_html_e( 'Select / Upload file', 'mf-downloadable-gallery' ); ?></button>
				<button type="button" class="button-link mfdg-remove-file" <?php echo $file_id ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Remove', 'mf-downloadable-gallery' ); ?></button>
				<div class="mfdg-file-preview">
					<?php if ( $file_id ) : ?>
						<a href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $file_name ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<hr />

		<div class="mfdg-field">
			<label class="mfdg-field__label">
				<input type="checkbox" name="mfdg_require_email" value="1" <?php checked( '1', (string) $require_email ); ?> />
				<?php esc_html_e( 'Require e-mail before download', 'mf-downloadable-gallery' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'When checked, visitors enter their e-mail to download this file. When unchecked, the file downloads directly.', 'mf-downloadable-gallery' ); ?>
			</p>
		</div>

		<div class="mfdg-field">
			<p class="description">
				<strong><?php esc_html_e( 'Cover image:', 'mf-downloadable-gallery' ); ?></strong>
				<?php esc_html_e( 'Set the cover using the “Featured image” box on the right (3:4 ratio recommended, e.g. 600×800).', 'mf-downloadable-gallery' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Save meta.
	 *
	 * @param int     $post_id Post id.
	 * @param WP_Post $post    Post object.
	 */
	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ), 'mfdg_save_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$file_id = isset( $_POST['mfdg_file_id'] ) ? absint( $_POST['mfdg_file_id'] ) : 0;
		update_post_meta( $post_id, '_mfdg_file_id', $file_id );

		$require_email = isset( $_POST['mfdg_require_email'] ) ? '1' : '0';
		update_post_meta( $post_id, '_mfdg_require_email', $require_email );
	}
}
