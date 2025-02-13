<?php

namespace Load_HTML_Files\Admin;

use Load_HTML_Files\Admin\Admin_Pages;
use Load_HTML_Files\Data\Cpt;


class Admin_Settings extends Admin_Pages {

	/** @var Admin_Settings $instance */
	private static $instance = null;

	protected $settings_page;
	protected $settings_page_id = 'html_files_page_load-html-files-settings';  // top level
	protected $option_group = 'load-html-files';
	protected $settings_title;


	public function __construct() {
		$this->settings_title = esc_html__( 'Load HTML files', 'load-html-files' );
		parent::__construct();
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function register_settings() {
		/* Register our setting. */
		register_setting(
			$this->option_group,                         /* Option Group */
			'load-html-files-settings',
			array(
				'sanitize_callback' => '@sanitize_settings',
				'default' => NULL,
			)
		);

		/* Add settings menu page */
		$this->settings_page = add_submenu_page(
			'edit.php?post_type=html_files',
			'Settings', /* Page Title */
			'Settings',                       /* Menu Title */
			'manage_options',                 /* Capability */
			'load-html-files-settings',                         /* Page Slug */
			array( $this, 'settings_page' )          /* Settings Page Function Callback */
		);
		register_setting(
			$this->option_group,                         /* Option Group */
			"{$this->option_group}-reset",
			array(
				'sanitize_callback' => '@reset_sanitize',
				'default' => NULL,
			) /* Sanitize Callback */
		);
	}

	public function delete_options() {
		delete_option( 'load-html-files-settings' );
	}

	public function add_meta_boxes() {
		add_meta_box(
			'information',                  /* Meta Box ID */
			esc_html__( 'How to use', 'load-html-files' ),               /* Title */
			array( $this, 'meta_box_information' ),  /* Function Callback */
			$this->settings_page_id,               /* Screen: Our Settings Page */
			'normal',                 /* Context */
			'default'                 /* Priority */
		);
		add_meta_box(
			'settings',                  /* Meta Box ID */
			__( 'Settings', 'load-html-files' ),               /* Title */
			array( $this, 'meta_box_settings' ),  /* Function Callback */
			$this->settings_page_id,               /* Screen: Our Settings Page */
			'normal',                 /* Context */
			'default'                 /* Priority */
		);
	}

	public function meta_box_information() {
		?>
        <table class="form-table">
            <tbody>
            <tr>
			<?php do_action('ffpl_ad_display') ?>
            </tr>
            <tr>
                <p><?php esc_html_e( 'This plugin periodically ( every 5 minutes) checks the unprocessed folder (wp-content/uploads/html_files/unprocessed ) for any files. The files can be in folders / subfolders', 'load-html-files' ); ?>
                </p>
                <p><?php esc_html_e( 'When a file is found it reads it and creates a new custom post using the html content, stripping any javascript and css. The post title is tehH1 heading from the file', 'load-html-files' ); ?>
                </p>
                <p><?php esc_html_e( 'Folders and subfolders are converted into hierarchical categories', 'load-html-files' ); ?>
                </p>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Files', 'load-html-files' ); ?></th>
                <td>
                    <span class="description"><?php esc_html_e( 'The input files are expected to be valid HTML pages, that you send to your server, e.g. by SFTP. If you send a file witheth same H1 tag as aprevious it will overwrite the revious. This is deliberate to allow updated versions.', 'load-html-files' ); ?></span>
                </td>

            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Housekeeping', 'load-html-files' ); ?></th>
                <td>
                    <p>
                        <span class="description"><?php esc_html_e( 'Once a file is converted to a custom post it is moved into tehe processed folder. If you process lots of files you may want to 93manually delete them occasionally', 'load-html-files' ); ?></span>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
		<?php
	}

	public function sanitize_settings( $settings ) {

		$options = get_option( 'load-html-files-settings', $this->option_defaults( 'load-html-files-settings' ) );

		$settings['generic_name']          = sanitize_text_field( $settings['generic_name'] );
		$settings['generic_name_singular'] = sanitize_text_field( $settings['generic_name_singular'] );
		$settings['post_slug']             = sanitize_text_field( $settings['post_slug'] );
		$settings['category_slug']         = sanitize_text_field( $settings['category_slug'] );
		if ( $options['post_slug'] != $settings['post_slug'] ) {
			$settings['post_slug_changed'] = true;
		}
		if ( $options['category_slug'] != $settings['category_slug'] ) {
			$settings['category_slug_changed'] = true;
		}

		return $settings;
	}

	public function option_defaults( $option ) {
		switch ( $option ) {
			case 'load-html-files-settings':
				return array(
					'generic_name'          => 'HTML files',
					'generic_name_singular' => 'HTML file',
					'post_slug'             => 'html_files',
					'category_slug'         => 'html_files_category',
					'post_slug_changed'     => false,
					'category_slug_changed' => false,
				);
			default:
				return false;
		}
	}

	public function meta_box_settings() {
		?>
		<?php
		$options = get_option( 'load-html-files-settings', $this->option_defaults( 'load-html-files-settings' ) );

		?>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Generic Name', 'load-html-files' ); ?></th>
                <td>
                    <input type="text"
                           class="regular-text"
                           name="load-html-files-settings[generic_name]"
                           id="load-html-files-settings[generic_name]"
                           value="<?php echo esc_attr( $options['generic_name'] ) ?>">
                    <p>
                        <span class="description"><?php esc_html_e( 'Name used in admin, default is "HTML files" change this to be relevant to your use', 'load-html-files' ); ?></span>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Generic Name - Singluar', 'load-html-files' ); ?></th>
                <td>
                    <input type="text"
                           class="regular-text"
                           name="load-html-files-settings[generic_name_singular]"
                           id="load-html-files-settings[generic_name_singular]"
                           value="<?php echo esc_attr( $options['generic_name_singular'] ) ?>">
                    <p>
                        <span class="description"><?php esc_html_e( 'Singular version of the name, default is "HTML file" change this to be relevant to your use', 'load-html-files' ); ?></span>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Files Slug', 'load-html-files' ); ?></th>
                <td>
                    <input type="text"
                           class="regular-text"
                           name="load-html-files-settings[post_slug]"
                           id="load-html-files-settings[post_slug]"
                           value="<?php echo esc_attr( $options['post_slug'] ) ?>">
                    <p>
                        <span class="description"><?php esc_html_e( 'The slug is used in the paths to the resulting posts generated from files', 'load-html-files' ); ?></span>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Category Slug', 'load-html-files' ); ?></th>
                <td>
                    <input type="text"
                           class="regular-text"
                           name="load-html-files-settings[category_slug]"
                           id="load-html-files-settings[category_slug]"
                           value="<?php echo esc_attr( $options['category_slug'] ); ?>">
                    <p>
                        <span class="description"><?php esc_html_e( 'The slug is used in the paths to the resulting categories generated by folder structures', 'load-html-files' ); ?></span>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
		<?php
	}
}

