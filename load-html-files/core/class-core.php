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

		// Get settings
		$options = get_option( 'load-html-files-settings', array( 'import_post_type' => 'html_files' ) );
		$image_data = array( 'content' => $content, 'first_image_id' => null );

		// Process images if any image handling settings are enabled
		if ( isset( $options['download_external_images'] ) || isset( $options['convert_relative_urls'] ) ) {
			$image_data = $this->process_images( $content, $file, $options );
			$content = $image_data['content'];
		}
		$post_type = isset( $options['import_post_type'] ) ? $options['import_post_type'] : 'html_files';

		// check if post exists with title
		$post_id = $this->post_exists_by_title( $title, $post_type );
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
			// Only set categories for html_files post type
			if ( $post_type === 'html_files' ) {
				// remove object terms and red add them
				wp_delete_object_term_relationships( $post_id, 'html_files_category' );
				wp_set_object_terms( $post_id,  $parents, 'html_files_category' );
			}
			// Set featured image if enabled and we have image data
			if ( isset( $options['set_featured_image'] ) && $options['set_featured_image'] && ! empty( $image_data['first_image_id'] ) ) {
				set_post_thumbnail( $post_id, $image_data['first_image_id'] );
			}

		} else {

			// create the post
			$post    = array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => $post_type,
			);
			$post = apply_filters('lhfp_insert_post', $post, $file, $parents, $dir, $html );
			$post_id = wp_insert_post( $post );
			if ( ! is_wp_error( $post_id ) ) {
				if ( $post_type === 'html_files' ) {
					// Only set categories for html_files post type
					wp_set_object_terms( $post_id, $parents, 'html_files_category' );
				}
				// Set featured image if enabled and we have image data
				if ( isset( $options['set_featured_image'] ) && $options['set_featured_image'] && ! empty( $image_data['first_image_id'] ) ) {
					set_post_thumbnail( $post_id, $image_data['first_image_id'] );
				}
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

	private function post_exists_by_title( $title, $post_type = 'html_files' ) {
		$args = array(
			'name'        => $title,
			'post_type'   => $post_type,
			'post_status' => 'publish',
			'numberposts' => 1
		);
		$posts = get_posts($args);

// $posts contains the post objects
		$post = isset( $posts[0] ) ? $posts[0] : null;  // $post is the post obj

		return $post ? $post->ID : false;
	}

	public function load_html_files_cron() {
		if ( ! wp_next_scheduled( 'load_html_files_cron' ) ) {
			wp_schedule_event( time(), 'load_html_files', 'load_html_files_cron' );
		}
	}

	/**
	 * Process images in HTML content
	 *
	 * @param string $content HTML content containing images
	 * @param string $file_path Path to the original HTML file
	 * @param array $options Plugin settings
	 * @return array Array with 'content' and 'first_image_id' keys
	 */
	private function process_images( $content, $file_path, $options ) {
		$d = new DOMDocument();
		libxml_use_internal_errors( true );
		$d->loadHTML( '<?xml encoding="UTF-8">' . $content );
		libxml_use_internal_errors( false );

		$images = $d->getElementsByTagName( 'img' );
		$first_image_id = null;
		$processed_images = array();

		foreach ( $images as $img ) {
			$src = $img->getAttribute( 'src' );
			if ( empty( $src ) ) {
				continue;
			}

			$original_src = $src;
			$new_src = $src;
			$attachment_id = null;

			// Convert relative URLs to absolute if enabled
			if ( isset( $options['convert_relative_urls'] ) && $options['convert_relative_urls'] && ! $this->is_absolute_url( $src ) ) {
				$new_src = $this->make_absolute_url( $src, $file_path );
			}

			// Download external images if enabled
			if ( isset( $options['download_external_images'] ) && $options['download_external_images'] && $this->is_external_url( $new_src ) ) {
				$attachment_id = $this->download_image_to_media_library( $new_src, $img->getAttribute( 'alt' ) );
				if ( $attachment_id ) {
					$new_src = wp_get_attachment_url( $attachment_id );

					// Store the first successfully downloaded image ID
					if ( $first_image_id === null ) {
						$first_image_id = $attachment_id;
					}
				}
			}

			// Update the image src if it changed
			if ( $new_src !== $original_src ) {
				$img->setAttribute( 'src', $new_src );
			}

			// Add responsive attributes if we have an attachment
			if ( $attachment_id ) {
				$img->setAttribute( 'data-attachment-id', $attachment_id );
				// Add srcset for responsive images
				$srcset = wp_get_attachment_image_srcset( $attachment_id );
				if ( $srcset ) {
					$img->setAttribute( 'srcset', $srcset );
				}
			}
		}

		// Get the modified HTML
		$body = $d->getElementsByTagName( 'body' )->item( 0 );
		if ( $body ) {
			$content = $d->saveHTML( $body );
			$content = str_replace( array( '<body>', '</body>' ), '', $content );
		}

		return array(
			'content' => $content,
			'first_image_id' => $first_image_id
		);
	}

	/**
	 * Check if a URL is absolute
	 *
	 * @param string $url URL to check
	 * @return bool True if absolute, false otherwise
	 */
	private function is_absolute_url( $url ) {
		return preg_match( '#^https?://#i', $url ) || strpos( $url, '//' ) === 0;
	}

	/**
	 * Check if a URL is external (not from this WordPress site)
	 *
	 * @param string $url URL to check
	 * @return bool True if external, false if local
	 */
	private function is_external_url( $url ) {
		if ( ! $this->is_absolute_url( $url ) ) {
			return false;
		}

		$site_url = parse_url( site_url() );
		$url_parts = parse_url( $url );

		// If no host in URL, it's relative/local
		if ( ! isset( $url_parts['host'] ) ) {
			return false;
		}

		// Compare hosts
		return $url_parts['host'] !== $site_url['host'];
	}

	/**
	 * Convert relative URL to absolute URL
	 *
	 * @param string $relative_url Relative URL
	 * @param string $base_file_path Base file path for resolving relative URLs
	 * @return string Absolute URL
	 */
	private function make_absolute_url( $relative_url, $base_file_path ) {
		// If it starts with //, prepend the protocol
		if ( strpos( $relative_url, '//' ) === 0 ) {
			return ( is_ssl() ? 'https:' : 'http:' ) . $relative_url;
		}

		// If it starts with /, it's relative to the domain root
		if ( strpos( $relative_url, '/' ) === 0 ) {
			return site_url( $relative_url );
		}

		// Otherwise, it's relative to the current directory
		// Try to use the uploads URL as base
		$upload_dir = wp_upload_dir();
		$base_url = $upload_dir['baseurl'];

		// If the file is in the unprocessed folder, adjust the base URL
		if ( strpos( $base_file_path, '/html_files/unprocessed' ) !== false ) {
			$base_url = $upload_dir['baseurl'] . '/html_files/unprocessed';
			$dir_parts = dirname( str_replace( $upload_dir['basedir'] . '/html_files/unprocessed', '', $base_file_path ) );
			if ( $dir_parts && $dir_parts !== '.' ) {
				$base_url .= $dir_parts;
			}
		}

		return trailingslashit( $base_url ) . $relative_url;
	}

	/**
	 * Download an external image to the WordPress media library
	 *
	 * @param string $image_url URL of the image to download
	 * @param string $alt_text Alt text for the image
	 * @return int|false Attachment ID on success, false on failure
	 */
	private function download_image_to_media_library( $image_url, $alt_text = '' ) {
		// Include required files
		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		// Check if we already downloaded this image
		$existing = $this->get_attachment_id_by_url( $image_url );
		if ( $existing ) {
			return $existing;
		}

		// Download the image
		$tmp = download_url( $image_url );
		if ( is_wp_error( $tmp ) ) {
			return false;
		}

		// Get the filename and extension
		$url_parts = parse_url( $image_url );
		$filename = basename( $url_parts['path'] );

		// If no extension, try to detect from mime type
		if ( ! preg_match( '/\.(jpg|jpeg|png|gif|webp|svg)$/i', $filename ) ) {
			$filetype = wp_check_filetype( $tmp );
			if ( $filetype['ext'] ) {
				$filename .= '.' . $filetype['ext'];
			} else {
				// Try to detect from file content
				$mime = mime_content_type( $tmp );
				$ext = '';
				switch ( $mime ) {
					case 'image/jpeg':
						$ext = 'jpg';
						break;
					case 'image/png':
						$ext = 'png';
						break;
					case 'image/gif':
						$ext = 'gif';
						break;
					case 'image/webp':
						$ext = 'webp';
						break;
				}
				if ( $ext ) {
					$filename .= '.' . $ext;
				}
			}
		}

		$file_array = array(
			'name' => $filename,
			'tmp_name' => $tmp
		);

		// Upload the image to media library
		$attachment_id = media_handle_sideload( $file_array, 0 );

		// Clean up temp file
		@unlink( $tmp );

		if ( is_wp_error( $attachment_id ) ) {
			return false;
		}

		// Set alt text if provided
		if ( ! empty( $alt_text ) ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
		}

		// Store the original URL for future reference
		update_post_meta( $attachment_id, '_source_url', $image_url );

		return $attachment_id;
	}

	/**
	 * Get attachment ID by URL
	 *
	 * @param string $url URL to search for
	 * @return int|false Attachment ID or false if not found
	 */
	private function get_attachment_id_by_url( $url ) {
		global $wpdb;

		// First, check if we have stored this URL before
		$attachment_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_source_url' AND meta_value = %s LIMIT 1",
				$url
			)
		);

		if ( $attachment_id ) {
			return $attachment_id;
		}

		// Try to find by guid (less reliable but worth trying)
		$attachment_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE guid = %s AND post_type = 'attachment' LIMIT 1",
				$url
			)
		);

		return $attachment_id ? $attachment_id : false;
	}



}