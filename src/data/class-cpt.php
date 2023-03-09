<?php

namespace Load_HTML_Files\Data;


use Load_HTML_Files\Admin\Admin_Settings;

class Cpt {

	/**
	 * @var Cpt
	 */
	private static $instance = null;

	public function __construct() {
	}

	public function add_cpt() {

		$options = get_option( 'load-html-files-settings', Admin_Settings::get_instance()->option_defaults( 'load-html-files-settings' ) );

		$labels = array(
			'name'               => $options['generic_name'],
			'singular_name'      => $options['generic_name_singular'],
			'menu_name'          => $options['generic_name'],
			'name_admin_bar'     => $options['generic_name'],
			'add_new'            => esc_html_x( 'Add New', 'HTML File', 'load-html-files' ),
			'add_new_item'       => esc_html_x( 'Add New', 'HTML File', 'load-html-files' ) . ' ' . $options['generic_name_singular'],
			'new_item'           => esc_html_x( 'New', 'HTML File', 'load-html-files' ) . ' ' . $options['generic_name_singular'],
			'edit_item'          => esc_html_x( 'Edit', 'HTML File', 'load-html-files' ) . ' ' . $options['generic_name_singular'],
			'view_item'          => esc_html_x( 'View', 'HTML File', 'load-html-files' ) . ' ' . $options['generic_name_singular'],
			'all_items'          => esc_html_x( 'All', 'HTML File', 'load-html-files' ) . ' ' . $options['generic_name'],
			'search_items'       => esc_html_x( 'Search', 'HTML Files', 'load-html-files' ) . ' ' . $options['generic_name'],
			'parent_item_colon'  => esc_html_x( 'Parent', 'HTML File', 'load-html-files' ) . ' ' . $options['generic_name_singular'],
			'not_found'          => esc_html_x( 'No', 'HTML Files', 'load-html-files' ) . ' ' . $options['generic_name'] . ' ' .
			                        esc_html_x( 'found.', 'HTML Files', 'load-html-files' ),
			'not_found_in_trash' => esc_html_x( 'No', 'HTML Files', 'load-html-files' ) . ' ' . $options['generic_name'] . ' ' .
			                        esc_html_x( 'found in trash.', 'HTML Files', 'load-html-files' ),
		);
		$args   = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'load-html-files' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'rewrite'            => array( 'slug' => $options['post_slug'] ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'taxonomies'         => array( $options['category_slug'] ),
			'supports'           => array( 'title' ),
		);
		register_post_type( 'html_files', $args );
		if ( isset( $options['post_slug_changed'] ) && $options['post_slug_changed'] ) {
			flush_rewrite_rules();
			$options['post_slug_changed'] = false;
			update_option( 'load-html-files-settings', $options );
		}
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function add_taxonomy() {
		$options = get_option( 'load-html-files-settings', Admin_Settings::get_instance()->option_defaults( 'load-html-files-settings' ) );

		// register html_files_category taxonomy
		$labels = array(
			'name'              => $options['generic_name'] . ' ' . esc_html_x( 'Categories', 'taxonomy general name', 'load-html-files' ),
			'singular_name'     => $options['generic_name'] . ' ' . esc_html_x( 'HTML Files Category', 'taxonomy singular name', 'load-html-files' ),
			'search_items'      => esc_html_x( 'Search', 'HTML Files', 'load-html-files' ) . ' ' . $options['generic_name'] . ' ' .
			                       esc_html_x( 'Categories', 'taxonomy general name', 'load-html-files' ),
			'all_items'         => esc_html_x( 'All', 'HTML Files', 'load-html-files' ) . ' ' . $options['generic_name'] . ' ' .
			                       esc_html_x( 'Categories', 'taxonomy general name', 'load-html-files' ),
			'parent_item'       => esc_html_x( 'Parent', 'HTML Files', 'load-html-files' ) . ' ' . $options['generic_name'] . ' ' .
			                       esc_html_x( 'Category', 'taxonomy general name', 'load-html-files' ),
			'parent_item_colon' => esc_html_x( 'Parent', 'HTML Files', 'load-html-files' ) . ' ' . $options['generic_name'] . ' ' .
			                       esc_html_x( 'Category:', 'taxonomy general name', 'load-html-files' ),
			'edit_item'         => esc_html_x( 'Edit', 'HTML Files', 'load-html-files' ) . ' ' . $options['generic_name'] . ' ' .
			                       esc_html_x( 'Category', 'taxonomy general name', 'load-html-files' ),
			'update_item'       => esc_html_x( 'Update', 'HTML Files', 'load-html-files' ) . ' ' . $options['generic_name'] . ' ' .
			                       esc_html_x( 'Category', 'taxonomy general name', 'load-html-files' ),
			'add_new_item'      => esc_html_x( 'Add New', 'HTML Files', 'load-html-files' ) . ' ' . $options['generic_name'] . ' ' .
			                       esc_html_x( 'Category', 'taxonomy general name', 'load-html-files' ),
			'new_item_name'     => esc_html_x( 'New', 'HTML Files', 'load-html-files' ) . ' ' . $options['generic_name'] . ' ' .
			                       esc_html_x( 'Category', 'taxonomy general name', 'load-html-files' ),
			'menu_name'         =>  $options['generic_name'] . ' ' .
			                       esc_html_x( 'Categories', 'taxonomy general name', 'load-html-files' ),
		);
		$args   = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => $options['category_slug'] ),
		);
		register_taxonomy( 'html_files_category', array( 'html_files' ), $args );
		if ( isset( $options['category_slug_changed'] ) && $options['category_slug_changed'] ) {
			flush_rewrite_rules();
			$options['category_slug_changed'] = false;
			update_option( 'load-html-files-settings', $options );
		}
         // delete all taxonomy terms
		$terms = get_terms( array(  'taxonomy' => 'html_files_category', 'hide_empty' => false, ) );
         			$a=1;
	}
}
