<?php
/**
 * Admin pages: leads list, settings, CSV export.
 *
 * @package MF_Downloadable_Gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MFDG_Admin
 */
class MFDG_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_post_mfdg_export', array( $this, 'export_csv' ) );
		add_action( 'admin_post_mfdg_save_settings', array( $this, 'save_settings' ) );
		add_action( 'admin_post_mfdg_delete_lead', array( $this, 'delete_lead' ) );

		add_filter( 'manage_' . MFDG_CPT::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . MFDG_CPT::POST_TYPE . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
	}

	/**
	 * Register submenus under the CPT.
	 */
	public function menu() {
		$parent = 'edit.php?post_type=' . MFDG_CPT::POST_TYPE;

		add_submenu_page(
			$parent,
			__( 'E-mail List', 'mf-downloadable-gallery' ),
			__( 'E-mail List', 'mf-downloadable-gallery' ),
			'manage_options',
			'mfdg-leads',
			array( $this, 'render_leads_page' )
		);

		add_submenu_page(
			$parent,
			__( 'Settings', 'mf-downloadable-gallery' ),
			__( 'Settings', 'mf-downloadable-gallery' ),
			'manage_options',
			'mfdg-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Admin list columns for the CPT.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['mfdg_cover'] = __( 'Cover', 'mf-downloadable-gallery' );
				$new['mfdg_gate']  = __( 'E-mail required', 'mf-downloadable-gallery' );
			}
		}
		return $new;
	}

	/**
	 * Render custom column content.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post id.
	 */
	public function column_content( $column, $post_id ) {
		if ( 'mfdg_cover' === $column ) {
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 45, 60 ) );
			} else {
				echo '&mdash;';
			}
		}
		if ( 'mfdg_gate' === $column ) {
			$req = get_post_meta( $post_id, '_mfdg_require_email', true );
			echo '1' === (string) $req
				? '<span class="dashicons dashicons-yes" style="color:#46b450"></span>'
				: '<span class="dashicons dashicons-minus" style="color:#999"></span>';
		}
	}

	/**
	 * Render the leads (e-mail list) page.
	 */
	public function render_leads_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$per_page = 25;

		$total   = MFDG_Leads::count( $search );
		$unique  = MFDG_Leads::count_unique();
		$rows    = MFDG_Leads::query(
			array(
				'search'   => $search,
				'per_page' => $per_page,
				'page'     => $paged,
			)
		);
		$pages   = max( 1, ceil( $total / $per_page ) );

		$export_url        = wp_nonce_url( admin_url( 'admin-post.php?action=mfdg_export&mode=all' ), 'mfdg_export' );
		$export_unique_url = wp_nonce_url( admin_url( 'admin-post.php?action=mfdg_export&mode=unique' ), 'mfdg_export' );
		?>
		<div class="wrap mfdg-admin-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'E-mail List', 'mf-downloadable-gallery' ); ?></h1>
			<a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action"><?php esc_html_e( 'Export CSV (all)', 'mf-downloadable-gallery' ); ?></a>
			<a href="<?php echo esc_url( $export_unique_url ); ?>" class="page-title-action"><?php esc_html_e( 'Export CSV (unique e-mails)', 'mf-downloadable-gallery' ); ?></a>
			<hr class="wp-header-end" />

			<p class="mfdg-stats">
				<?php
				printf(
					/* translators: 1: total records, 2: unique e-mails */
					esc_html__( 'Total records: %1$s · Unique e-mails: %2$s', 'mf-downloadable-gallery' ),
					'<strong>' . esc_html( number_format_i18n( $total ) ) . '</strong>',
					'<strong>' . esc_html( number_format_i18n( $unique ) ) . '</strong>'
				);
				?>
			</p>

			<form method="get">
				<input type="hidden" name="post_type" value="<?php echo esc_attr( MFDG_CPT::POST_TYPE ); ?>" />
				<input type="hidden" name="page" value="mfdg-leads" />
				<p class="search-box">
					<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search e-mail or file', 'mf-downloadable-gallery' ); ?>" />
					<button type="submit" class="button"><?php esc_html_e( 'Search', 'mf-downloadable-gallery' ); ?></button>
				</p>
			</form>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'E-mail', 'mf-downloadable-gallery' ); ?></th>
						<th><?php esc_html_e( 'Name', 'mf-downloadable-gallery' ); ?></th>
						<th><?php esc_html_e( 'File', 'mf-downloadable-gallery' ); ?></th>
						<th><?php esc_html_e( 'Category', 'mf-downloadable-gallery' ); ?></th>
						<th><?php esc_html_e( 'Consent', 'mf-downloadable-gallery' ); ?></th>
						<th><?php esc_html_e( 'Date', 'mf-downloadable-gallery' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $rows ) ) : ?>
						<tr><td colspan="7"><?php esc_html_e( 'No records yet.', 'mf-downloadable-gallery' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $rows as $row ) : ?>
							<?php
							$delete_url = wp_nonce_url(
								admin_url( 'admin-post.php?action=mfdg_delete_lead&id=' . (int) $row->id ),
								'mfdg_delete_lead_' . (int) $row->id
							);
							?>
							<tr>
								<td><a href="mailto:<?php echo esc_attr( $row->email ); ?>"><?php echo esc_html( $row->email ); ?></a></td>
								<td><?php echo esc_html( $row->name ); ?></td>
								<td><?php echo esc_html( $row->file_title ); ?></td>
								<td><?php echo esc_html( $row->category ); ?></td>
								<td><?php echo $row->consent ? '&#10003;' : '&mdash;'; ?></td>
								<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' H:i', $row->created_at ) ); ?></td>
								<td><a href="<?php echo esc_url( $delete_url ); ?>" class="mfdg-delete" onclick="return confirm('<?php echo esc_js( __( 'Delete this record?', 'mf-downloadable-gallery' ) ); ?>');"><?php esc_html_e( 'Delete', 'mf-downloadable-gallery' ); ?></a></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ( $pages > 1 ) : ?>
				<div class="tablenav"><div class="tablenav-pages">
					<?php
					$base = add_query_arg(
						array(
							'post_type' => MFDG_CPT::POST_TYPE,
							'page'      => 'mfdg-leads',
							's'         => $search,
							'paged'     => '%#%',
						),
						admin_url( 'edit.php' )
					);
					echo wp_kses_post(
						paginate_links(
							array(
								'base'      => $base,
								'format'    => '',
								'current'   => $paged,
								'total'     => $pages,
								'prev_text' => '&laquo;',
								'next_text' => '&raquo;',
							)
						)
					);
					?>
				</div></div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s = MFDG_Settings::all();
		?>
		<div class="wrap mfdg-admin-wrap">
			<h1><?php esc_html_e( 'Downloadable Gallery Settings', 'mf-downloadable-gallery' ); ?></h1>

			<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'mf-downloadable-gallery' ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="mfdg_save_settings" />
				<?php wp_nonce_field( 'mfdg_save_settings' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Require e-mail by default', 'mf-downloadable-gallery' ); ?></th>
						<td>
							<label><input type="checkbox" name="default_require_email" value="1" <?php checked( 1, $s['default_require_email'] ); ?> /> <?php esc_html_e( 'New files require an e-mail unless unchecked per file.', 'mf-downloadable-gallery' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Collect visitor name', 'mf-downloadable-gallery' ); ?></th>
						<td><label><input type="checkbox" name="collect_name" value="1" <?php checked( 1, $s['collect_name'] ); ?> /> <?php esc_html_e( 'Also ask for a name in the download form.', 'mf-downloadable-gallery' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Remember visitor', 'mf-downloadable-gallery' ); ?></th>
						<td><label><input type="checkbox" name="remember_visitor" value="1" <?php checked( 1, $s['remember_visitor'] ); ?> /> <?php esc_html_e( 'After the first submission, do not ask the same visitor again during their session.', 'mf-downloadable-gallery' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><label for="mfdg_consent_enabled"><?php esc_html_e( 'Consent checkbox', 'mf-downloadable-gallery' ); ?></label></th>
						<td>
							<label><input type="checkbox" id="mfdg_consent_enabled" name="consent_enabled" value="1" <?php checked( 1, $s['consent_enabled'] ); ?> /> <?php esc_html_e( 'Show a required consent checkbox (recommended for KVKK/GDPR).', 'mf-downloadable-gallery' ); ?></label>
							<p><textarea name="consent_text" rows="2" class="large-text"><?php echo esc_textarea( $s['consent_text'] ); ?></textarea></p>
							<p class="description"><?php esc_html_e( 'Basic HTML links are allowed in the consent text.', 'mf-downloadable-gallery' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mfdg_button_label"><?php esc_html_e( 'Direct download button', 'mf-downloadable-gallery' ); ?></label></th>
						<td><input type="text" id="mfdg_button_label" name="button_label" value="<?php echo esc_attr( $s['button_label'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="mfdg_gated_button_label"><?php esc_html_e( 'Gated (e-mail) button', 'mf-downloadable-gallery' ); ?></label></th>
						<td><input type="text" id="mfdg_gated_button_label" name="gated_button_label" value="<?php echo esc_attr( $s['gated_button_label'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="mfdg_success_message"><?php esc_html_e( 'Success message', 'mf-downloadable-gallery' ); ?></label></th>
						<td><input type="text" id="mfdg_success_message" name="success_message" value="<?php echo esc_attr( $s['success_message'] ); ?>" class="large-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="mfdg_columns"><?php esc_html_e( 'Default columns', 'mf-downloadable-gallery' ); ?></label></th>
						<td><input type="number" id="mfdg_columns" name="columns" value="<?php echo esc_attr( $s['columns'] ); ?>" min="1" max="6" class="small-text" /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Admin notification', 'mf-downloadable-gallery' ); ?></th>
						<td>
							<label><input type="checkbox" name="notify_admin" value="1" <?php checked( 1, $s['notify_admin'] ); ?> /> <?php esc_html_e( 'E-mail me when a new lead is captured.', 'mf-downloadable-gallery' ); ?></label>
							<p><input type="email" name="notify_email" value="<?php echo esc_attr( $s['notify_email'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Notification address', 'mf-downloadable-gallery' ); ?>" /></p>
						</td>
					</tr>
				</table>

				<?php submit_button( __( 'Save settings', 'mf-downloadable-gallery' ) ); ?>
			</form>

			<hr />
			<h2><?php esc_html_e( 'Shortcodes', 'mf-downloadable-gallery' ); ?></h2>
			<p><?php esc_html_e( 'Use these shortcodes on any page or post:', 'mf-downloadable-gallery' ); ?></p>
			<table class="widefat striped" style="max-width:820px">
				<tbody>
					<tr><td><code>[mf_downloadable_gallery]</code></td><td><?php esc_html_e( 'All files as a grid, newest first, with a category filter bar.', 'mf-downloadable-gallery' ); ?></td></tr>
					<tr><td><code>[mf_downloadable_gallery layout="grouped"]</code></td><td><?php esc_html_e( 'Files grouped under category headings.', 'mf-downloadable-gallery' ); ?></td></tr>
					<tr><td><code>[mf_downloadable_gallery category="brosurler"]</code></td><td><?php esc_html_e( 'Only files from a specific category (slug).', 'mf-downloadable-gallery' ); ?></td></tr>
					<tr><td><code>[mf_downloadable_gallery columns="4" filter="no" order="ASC"]</code></td><td><?php esc_html_e( 'Custom columns, no filter bar, oldest first.', 'mf-downloadable-gallery' ); ?></td></tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Save settings.
	 */
	public function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'mf-downloadable-gallery' ) );
		}
		check_admin_referer( 'mfdg_save_settings' );

		MFDG_Settings::save( wp_unslash( $_POST ) );

		wp_safe_redirect( admin_url( 'edit.php?post_type=' . MFDG_CPT::POST_TYPE . '&page=mfdg-settings&updated=1' ) );
		exit;
	}

	/**
	 * Delete a single lead.
	 */
	public function delete_lead() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'mf-downloadable-gallery' ) );
		}
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		check_admin_referer( 'mfdg_delete_lead_' . $id );

		MFDG_Leads::delete( $id );
		wp_safe_redirect( admin_url( 'edit.php?post_type=' . MFDG_CPT::POST_TYPE . '&page=mfdg-leads' ) );
		exit;
	}

	/**
	 * Export leads as CSV.
	 */
	public function export_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'mf-downloadable-gallery' ) );
		}
		check_admin_referer( 'mfdg_export' );

		$mode   = isset( $_GET['mode'] ) && 'unique' === $_GET['mode'] ? 'unique' : 'all';
		$unique = ( 'unique' === $mode );
		$rows   = MFDG_Leads::all( $unique );

		$filename = 'mfdg-emails-' . $mode . '-' . gmdate( 'Y-m-d' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$out = fopen( 'php://output', 'w' );
		// UTF-8 BOM for Excel compatibility with Turkish characters.
		fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		fputcsv( $out, array( 'Email', 'Name', 'File', 'Category', 'Consent', 'IP', 'Date' ) );

		foreach ( $rows as $row ) {
			fputcsv(
				$out,
				array(
					$row->email,
					$row->name,
					$row->file_title,
					$row->category,
					$row->consent ? 'yes' : 'no',
					$row->ip,
					$row->created_at,
				)
			);
		}

		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}
}
