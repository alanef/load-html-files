<?php

namespace Load_HTML_Files\Control;

class Activator {

	/**
	 *
	 */
	public static function activate() {
		$upload_dir = wp_upload_dir();
		// get filesystem credentials
		$creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, array() );
		if ( ! WP_Filesystem( $creds ) ) {
			return false;
		}
		 /** @var \WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		// create upload folder if it does not exist
		if ( ! $wp_filesystem->exists( $upload_dir['basedir'] . '/html_files' ) ) {
			$wp_filesystem->mkdir( $upload_dir['basedir'] . '/html_files' );
		}
		// if unprocessed folder does not exist make it
		if ( ! $wp_filesystem->exists( $upload_dir['basedir'] . '/html_files/unprocessed' ) ) {
			$wp_filesystem->mkdir( $upload_dir['basedir'] . '/html_files/unprocessed' );
		}
		// if processed folder does not exist make it
		if ( ! $wp_filesystem->exists( $upload_dir['basedir'] . '/html_files/processed' ) ) {
			$wp_filesystem->mkdir( $upload_dir['basedir'] . '/html_files/processed' );
		}
	}
}