<?php

namespace Load_HTML_Files\Core;


use DOMDocument;

class Core {

	/**
	 * @var Core
	 */
	private static $instance = null;

	/**
	 * @var array
	 */
	private $upload_dir;


	public function __construct() {
		$this->upload_dir = wp_upload_dir();

	}
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function hooks() {


	}


	public function load_html_files() {

		if ( ! function_exists( 'request_filesystem_credentials' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		// get filesystem credentials
		$creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, array() );
		if ( ! WP_Filesystem( $creds ) ) {
			return false;
		}
		// if there are any files, process them
		$files = list_files( $this->upload_dir['basedir'] . '/html_files/unprocessed' );
		$a     = 1;
		if ( ! empty( $files ) ) {
			$this->process_files( $files );
		}


	}

	private function process_files( $files ) {

		global $wp_filesystem;
		if ( $files ) {
			// loop through files  use wp_filesystem
			// for each directory create a directory in the upload processed dir if it doesn't exist

			foreach ( $files as $file ) {
				// get the directory parts after $this->upload_dir['basedir'] . '/html_files/unprocessed'
				$dir_parts = explode( $this->upload_dir['basedir'] . '/html_files/unprocessed', $file );
				// then get the directory parts
				$dir_parts = explode( '/', $dir_parts[1] );
				// remove the last part as it is the file name
				array_pop( $dir_parts );
				// remove the first part as it is empty
				array_shift( $dir_parts );
				// loop through the parts and create the directory if it doesn't exist
				$dir    = $this->upload_dir['basedir'] . '/html_files/processed';
				$parent = 0;
				$parents = array();
				foreach ( $dir_parts as $dir_part ) {
					$dir .= '/' . $dir_part;
					if ( ! $wp_filesystem->exists( $dir ) ) {
						$wp_filesystem->mkdir( $dir );
					}
					$parent = $this->add_term( $dir_part, $parent );
					$parents[] = (int) $parent;

				}
				// if a file i.e.doesn't end in / then process it
				if ( ! $wp_filesystem->is_dir( $file ) ) {
					$this->create_post( $wp_filesystem, $file, $parents, $dir );
				}


			}


		}

	}

	private function add_term( $term, $parent ) {
		$term_obj = term_exists( $term, 'html_files_category' );
		if ( $term_obj !== 0 && $term_obj !== null ) {
			$parent = $term_obj['term_id'];
		} else {
			$term_obj = wp_insert_term( $term, 'html_files_category', array( 'parent' => $parent ) );
			if ( ! is_wp_error( $term_obj ) ) {
				$parent = $term_obj['term_id'];
			}
		}

		return $parent;
	}

	/**
	 * @param $wp_filesystem
	 * @param $file
	 * @param $parent
	 * @param $dir_parts
	 *
	 * @return void
	 */
	public function create_post( $wp_filesystem, $file, $parents, $dir ) {
// get the file contents
		$html = $wp_filesystem->get_contents( $file );
		// get the title
		$title = $this->get_text_between_tags( $html, 'h1' );
		$title = htmlspecialchars( sanitize_text_field( $title[0] ) );
		// remove the h1 tags
		$html = $this->remove_tag_from_html( $html, 'h1' );
		// remove the inline script tags
		$html = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $html );
		// remove the inline style tags
		$html = preg_replace( '#<style(.*?)>(.*?)</style>#is', '', $html );
		// get the content
		$content = $this->kses( $this->get_body( $html, 'body' ) );

		// remove the start and end body tags
		$content = str_replace( '<body>', '', $content );
		$content = str_replace( '</body>', '', $content );

		// check if post exists with title
		$post_id = $this->post_exists_by_title( $title );
		if ( $post_id ) {
			// update the post
			// set post date to now
			$post = array(
				'ID'           => $post_id,
				'post_content' => $content,
				'post_date'    => gmdate( 'Y-m-d H:i:s' ),
				'post_date_gmt' => gmdate( 'Y-m-d H:i:s' ),
				'post_modified' => gmdate( 'Y-m-d H:i:s' ),
				'post_modified_gmt' => gmdate( 'Y-m-d H:i:s' ),
			);
			wp_update_post( $post );
			// remove object terms and red add them
			wp_delete_object_term_relationships( $post_id, 'html_files_category' );
			wp_set_object_terms( $post_id,  $parents, 'html_files_category' );


		} else {

			// create the post
			$post    = array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'html_files',
			);
			$post_id = wp_insert_post( $post );
			if ( ! is_wp_error( $post_id ) ) {
				wp_set_object_terms( $post_id, $parents, 'html_files_category' );
			}
		}
		// move the file to the processed directory
		$a =$wp_filesystem->move( $file,  $dir . '/' . basename( $file ), true );
	$b=1;
	}

	private function get_text_between_tags( $html, $tagname ) {
		$d = new DOMDocument();
		libxml_use_internal_errors( true );
		$d->loadHTML( $html );
		libxml_use_internal_errors( false );
		$return = array();
		foreach ( $d->getElementsByTagName( $tagname ) as $item ) {
			$return[] = $item->textContent;
		}

		return $return;
	}

	private function remove_tag_from_html( $html, $tagname ) {
		$d = new DOMDocument();
		libxml_use_internal_errors( true );
		$d->loadHTML( $html );
		libxml_use_internal_errors( false );
		$return = array();
		foreach ( $d->getElementsByTagName( $tagname ) as $item ) {
			$item->parentNode->removeChild( $item );
		}

		return $d->saveHTML();
	}

	private function kses( $html ) {
		$allowed_html = apply_filters( 'lhfp_kses', array() );
		// get post allowed html
		$allowed_html = array_merge( $allowed_html, wp_kses_allowed_html( 'post' ) );

		return wp_kses( $html, $allowed_html );

	}

	private function get_body( $html ) {
		$d = new DOMDocument();
		libxml_use_internal_errors( true );
		$d->loadHTML( $html );
		libxml_use_internal_errors( false );
		$bodies = $d->getElementsByTagName( 'body' );
		assert( $bodies->length === 1 );
		$body = $bodies->item( 0 );

		return $d->saveHTML( $body );

	}

	private function post_exists_by_title( $title ) {
		$post = get_page_by_title( $title, OBJECT, 'html_files' );

		return $post ? $post->ID : false;
	}

	public function load_html_files_cron() {
		if ( ! wp_next_scheduled( 'load_html_files_cron' ) ) {
			wp_schedule_event( time(), 'load_html_files', 'load_html_files_cron' );
		}
	}



}