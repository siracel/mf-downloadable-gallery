<?php
/**
 * Lead (e-mail) storage and export.
 *
 * @package MF_Downloadable_Gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MFDG_Leads
 */
class MFDG_Leads {

	/**
	 * Get the leads table name.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'mfdg_leads';
	}

	/**
	 * Create the leads table.
	 */
	public static function create_table() {
		global $wpdb;

		$table           = self::table();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			email VARCHAR(191) NOT NULL,
			name VARCHAR(191) DEFAULT '' NOT NULL,
			file_id BIGINT(20) UNSIGNED DEFAULT 0 NOT NULL,
			file_title VARCHAR(255) DEFAULT '' NOT NULL,
			category VARCHAR(255) DEFAULT '' NOT NULL,
			consent TINYINT(1) DEFAULT 0 NOT NULL,
			ip VARCHAR(100) DEFAULT '' NOT NULL,
			created_at DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id),
			KEY email (email),
			KEY file_id (file_id),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Insert a lead record.
	 *
	 * @param array $data Lead data.
	 * @return int|false Insert id or false.
	 */
	public static function insert( $data ) {
		global $wpdb;

		$defaults = array(
			'email'      => '',
			'name'       => '',
			'file_id'    => 0,
			'file_title' => '',
			'category'   => '',
			'consent'    => 0,
			'ip'         => '',
			'created_at' => current_time( 'mysql' ),
		);
		$data = wp_parse_args( $data, $defaults );

		$inserted = $wpdb->insert(
			self::table(),
			array(
				'email'      => sanitize_email( $data['email'] ),
				'name'       => sanitize_text_field( $data['name'] ),
				'file_id'    => absint( $data['file_id'] ),
				'file_title' => sanitize_text_field( $data['file_title'] ),
				'category'   => sanitize_text_field( $data['category'] ),
				'consent'    => $data['consent'] ? 1 : 0,
				'ip'         => sanitize_text_field( $data['ip'] ),
				'created_at' => $data['created_at'],
			),
			array( '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s' )
		);

		return $inserted ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Query leads.
	 *
	 * @param array $args Query args (search, per_page, page, orderby, order).
	 * @return array Rows.
	 */
	public static function query( $args = array() ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'search'   => '',
				'per_page' => 25,
				'page'     => 1,
				'orderby'  => 'created_at',
				'order'    => 'DESC',
			)
		);

		$table    = self::table();
		$where    = 'WHERE 1=1';
		$prepare  = array();

		if ( '' !== $args['search'] ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where   .= ' AND (email LIKE %s OR name LIKE %s OR file_title LIKE %s)';
			$prepare  = array( $like, $like, $like );
		}

		$allowed_orderby = array( 'created_at', 'email', 'file_title', 'id' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order           = ( 'ASC' === strtoupper( $args['order'] ) ) ? 'ASC' : 'DESC';

		$per_page = max( 1, absint( $args['per_page'] ) );
		$page     = max( 1, absint( $args['page'] ) );
		$offset   = ( $page - 1 ) * $per_page;

		$sql = "SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";

		$prepare[] = $per_page;
		$prepare[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $wpdb->prepare( $sql, $prepare ) );
	}

	/**
	 * Count leads matching a search.
	 *
	 * @param string $search Search term.
	 * @return int
	 */
	public static function count( $search = '' ) {
		global $wpdb;
		$table = self::table();

		if ( '' !== $search ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE email LIKE %s OR name LIKE %s OR file_title LIKE %s", $like, $like, $like ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	/**
	 * Count unique e-mail addresses.
	 *
	 * @return int
	 */
	public static function count_unique() {
		global $wpdb;
		$table = self::table();
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(DISTINCT email) FROM {$table}" );
	}

	/**
	 * Get all rows for export.
	 *
	 * @param bool $unique Only unique e-mails (latest record per e-mail).
	 * @return array
	 */
	public static function all( $unique = false ) {
		global $wpdb;
		$table = self::table();

		if ( $unique ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return $wpdb->get_results( "SELECT * FROM {$table} WHERE id IN (SELECT MAX(id) FROM {$table} GROUP BY email) ORDER BY created_at DESC" );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC" );
	}

	/**
	 * Delete a lead by id.
	 *
	 * @param int $id Lead id.
	 * @return bool
	 */
	public static function delete( $id ) {
		global $wpdb;
		return (bool) $wpdb->delete( self::table(), array( 'id' => absint( $id ) ), array( '%d' ) );
	}
}
