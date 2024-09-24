<?php

namespace Load_HTML_Files\Control;

use WP_Filesystem;

class Uninstall {

	/**
	 *
	 */
	public static function uninstall() {


		// remove all posts of custom post type html_files
		$args  = array(
			'post_type'      => 'html_files',
			'posts_per_page' => - 1,
			'post_status'    => 'any'
		);
		$posts = get_posts( $args );
		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
		// register the term so we can then delete it
		register_taxonomy( 'html_files_category', 'html_files' );
		// remove all custom taxonomies
		$terms = get_terms( 'html_files_category' );
		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, 'html_files_category' );
		}
		// remove all custom post types
		unregister_post_type( 'html_files' );
		// remove all custom taxonomies
		unregister_taxonomy( 'html_files_category' );



		// delete options
		delete_option( 'load-html-files-settings' );
		// remove upload dirs
		// get filesystem credentials
		$creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, array() );
		if ( ! WP_Filesystem( $creds ) ) {
			return false;
		}
		global $wp_filesystem;


		$upload_dir = wp_upload_dir();
		$wp_filesystem->delete( $upload_dir['basedir'] . '/html_files', true );
	}

}
