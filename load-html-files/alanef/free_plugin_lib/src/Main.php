<?php

namespace Fullworks_Free_Plugin_Lib;

class Main {
	/**
	 * @var mixed
	 */
	private static $plugin_shortname;
	/**
	 * @var mixed
	 */
	private $page;
	/**
	 * @var \WP_Screen|null
	 */
	private $current_page;
	/**
	 * @var mixed
	 */
	private $plugin_file;
	/**
	 * @var mixed
	 */
	private $settings_page;

	public function __construct( $plugin_file, $settings_page, $plugin_shortname, $page ) {
		self::$plugin_shortname = $plugin_shortname;
		$this->page             = $page;
		$this->plugin_file      = $plugin_file;
		$this->settings_page    = $settings_page;
		register_activation_hook( $this->plugin_file, array( $this, 'plugin_activate' ) );
		register_uninstall_hook( $this->plugin_file, array( '\Fullworks_Free_Plugin_Lib\Main', 'plugin_uninstall' ) );
		add_filter( 'plugin_action_links_' . $this->plugin_file, array( $this, 'settings_link' ) );
		add_action( 'init', array( $this, 'load_text_domain' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'current_screen', function () {
			$this->current_page = get_current_screen();
			if ( is_admin() && $this->current_page->id === 'settings_page_ffpl-opt-in' ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			}

			if ( is_admin() && $this->current_page->id === $this->page ) {
				if ( ! get_site_option( self::$plugin_shortname . '_form_rendered' ) ) {
					wp_safe_redirect( admin_url( 'options-general.php?page=ffpl-opt-in' ) );
				}
			}
		} );
	}

	public function plugin_activate() {
		if ( ! get_site_option( self::$plugin_shortname . '_form_rendered' ) ) {
			if ( ! isset( $_GET['activate-multi'] ) ) {
				wp_safe_redirect( admin_url( $this->settings_page ) );
			}
		}
	}

	public static function plugin_uninstall() {
		delete_site_option( self::$plugin_shortname . '_form_rendered' );
	}

	public function settings_link( $links ) {
		$settings_link = '<a href="' . esc_url( $this->settings_page ) . '">' . esc_html( $this->translate( 'Settings' ) ) . '</a>';
		array_unshift(
			$links,
			$settings_link
		);

		return $links;
	}


	public function add_settings_page() {
		if ( ! get_site_option( self::$plugin_shortname . '_form_rendered' ) ) {
			add_options_page(
				esc_html( $this->translate( 'Opt In' ) ), // Page title
				esc_html( $this->translate( 'Opt In' ) ), // Menu title
				'manage_options', // Capability
				'ffpl-opt-in', // Menu slug
				array( $this, 'render_opt_in_page' ) // Callback function
			);
		}
	}

	public function load_text_domain() {
		load_plugin_textdomain( 'free-plugin-lib', false, __DIR__ . '../languages' );
	}

	public function render_opt_in_page() {
        $acc="aaa";
        printf("%s",$acc);
		?>
            <div class="wrap">
        <div class="fpl-wrap">
            <div class="box">
                <div class="logo-container">
                    <img src="path/to/logo.png" alt="Logo" class="logo">
                </div>
                <div class="box-content">
                    <p>Would you like to opt in to our service?</p>

                        <form method="post">
                            <div class="button-wrap">
	                        <?php
	                        // Generate the nonce field
	                        wp_nonce_field('my_form_action', 'my_form_nonce');
	                        ?>
                        <button class="button button-primary btn-optin" name="action" value="optin" tabindex="1" type="submit">Allow &amp; Continue</button>
                        <button class="button button-secondary btn-skip" name="action" value="skip" tabindex="2" type="submit">Skip</button>
                            </div>
                                <form>
                    </div>
                    <a href="#" class="details-link" id="detailsLink">Privacy & Details</a>
                    <div class="details-content" id="detailsContent">
                        <p>Here are more details about our service...</p>
                    </div>
                </div>
            </div>
        </div>
        </div>

		<?php
	}


	public function enqueue_assets() {

		$base_url = plugin_dir_url(__FILE__) . '../src/Assets/';

		// Enqueue a CSS file from the ../Assets/css directory
		wp_enqueue_style( 'ffpl-style-css', $base_url . 'css/style.css' );

		// Enqueue a JavaScript file from the ../Assets/scripts directory
		wp_enqueue_script( 'ffpl-main-js', $base_url . 'scripts/main.js', [], false, true );
	}


	private function translate( $text ) {
		// deliberately done like this to stop polygots auto adding to translation files as
		// wp.org doesn't differentiate text domains
		return translate( $text, 'free-plugin-lib' );
	}
}