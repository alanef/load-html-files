<?php


/**
 * The admin-specific functionality of the plugin.
 *
 *
 */

namespace Load_HTML_Files\Admin;

class Admin {

	/** @var Admin $instance */
	private static $instance = null;


	public function __construct() {
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( LOAD_HTML_FILES_PLUGIN_BASENAME . '-admin', LOAD_HTML_FILES_PLUGIN_URL . 'ui/admin/css/style.css', array(), LOAD_HTML_FILES_PLUGIN_VERSION, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( LOAD_HTML_FILES_PLUGIN_BASENAME . '-admin', LOAD_HTML_FILES_PLUGIN_URL . 'ui/admin/js/script.js', array( 'jquery' ), LOAD_HTML_FILES_PLUGIN_VERSION, false );
	}

	public function add_cron_schedule( $schedules ) {
		$schedules['load_html_files'] = array(
			'interval' => apply_filters( 'load_html_files_schedule', 5 * MINUTE_IN_SECONDS ),
			'display'  => esc_html__( 'Load Sailwave Custom Schedule', 'load-html-files' )
		);

		return $schedules;
	}

	// Add a custom HTML editor for the CPT

	public function add_html_editor() {
		add_meta_box(
			'load_html_files_editor',
			esc_html__( 'HTML Editor', 'load-html-files' ),
			array( $this, 'html_editor_callback' ),
			'html_files',
			'normal',
			'high' );
	}

	public function html_editor_callback( $post ) {
		// use wp_editor as html only editor
		wp_editor( $post->post_content, 'content', array(
			'textarea_name' => 'content',
			'textarea_rows' => 20,
			'media_buttons' => false,
			'teeny'         => false,
			'quicktags'     => true,
			'tinymce'       => false,
		) );
	}
}




