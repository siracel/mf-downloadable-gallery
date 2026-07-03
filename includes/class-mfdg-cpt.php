<?php
/**
 * Custom post type + taxonomy.
 *
 * @package MF_Downloadable_Gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MFDG_CPT
 */
class MFDG_CPT {

	const POST_TYPE = 'mfdg_file';
	const TAXONOMY  = 'mfdg_category';

	/**
	 * Register post type and taxonomy.
	 */
	public static function register() {
		self::register_post_type();
		self::register_taxonomy();
	}

	/**
	 * Register the downloadable file post type.
	 */
	private static function register_post_type() {
		$labels = array(
			'name'               => __( 'Downloadable Files', 'mf-downloadable-gallery' ),
			'singular_name'      => __( 'Downloadable File', 'mf-downloadable-gallery' ),
			'menu_name'          => __( 'Downloads', 'mf-downloadable-gallery' ),
			'add_new'            => __( 'Add New', 'mf-downloadable-gallery' ),
			'add_new_item'       => __( 'Add New File', 'mf-downloadable-gallery' ),
			'edit_item'          => __( 'Edit File', 'mf-downloadable-gallery' ),
			'new_item'           => __( 'New File', 'mf-downloadable-gallery' ),
			'view_item'          => __( 'View File', 'mf-downloadable-gallery' ),
			'search_items'       => __( 'Search Files', 'mf-downloadable-gallery' ),
			'not_found'          => __( 'No files found', 'mf-downloadable-gallery' ),
			'not_found_in_trash' => __( 'No files found in Trash', 'mf-downloadable-gallery' ),
			'all_items'          => __( 'All Files', 'mf-downloadable-gallery' ),
			'featured_image'     => __( 'Cover Image (3:4)', 'mf-downloadable-gallery' ),
			'set_featured_image' => __( 'Set cover image', 'mf-downloadable-gallery' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'has_archive'        => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-download',
			'menu_position'      => 26,
			'capability_type'    => 'post',
			'hierarchical'       => false,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'rewrite'            => array( 'slug' => 'downloads' ),
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Register the category taxonomy.
	 */
	private static function register_taxonomy() {
		$labels = array(
			'name'          => __( 'File Categories', 'mf-downloadable-gallery' ),
			'singular_name' => __( 'File Category', 'mf-downloadable-gallery' ),
			'menu_name'     => __( 'Categories', 'mf-downloadable-gallery' ),
			'all_items'     => __( 'All Categories', 'mf-downloadable-gallery' ),
			'edit_item'     => __( 'Edit Category', 'mf-downloadable-gallery' ),
			'add_new_item'  => __( 'Add New Category', 'mf-downloadable-gallery' ),
			'search_items'  => __( 'Search Categories', 'mf-downloadable-gallery' ),
		);

		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'download-category' ),
		);

		register_taxonomy( self::TAXONOMY, array( self::POST_TYPE ), $args );
	}
}
