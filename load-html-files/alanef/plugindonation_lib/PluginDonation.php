<?php
/*
 *  @version 1.6
 *  @licence GPL2 or Later
 *  @copyright Alan Fuller
 */

namespace AlanEFPluginDonation;

/**
 * @since 1.0
 */
class PluginDonation {
	public $strings;
	/**
	 * @var string $plugin_slug plugin base name or slug
	 */
	protected $plugin_slug;
	/**
	 * @var string $settings_hook the page hook for the plugin settings page
	 */
	protected $settings_hook;
	/**
	 * @var string $plugin_file the full plugin path file e.g. my-plugin/my-plugin.php
	 */
	protected $plugin_file;
	/**
	 * @var string $settings_url full url to setiings page with donate info
	 */
	protected $settings_url;
	/**
	 * @var string $title the plugin name in human form
	 */
	protected $title;
	protected $freemius;
	protected $repo;

	/**
	 * @param string $plugin_slug plugin base name or slug
	 * @param string $settings_hook the page hook for the plugin settings page
	 * @param string $plugin_file the full plugin path file e.g. my-plugin/my-plugin.php
	 * @param string $settings_url the full url for a page with information on how to donate
	 * @param string $title the plugin name in human form
	 *
	 * @since 1.0
	 */
	public function __construct( $plugin_slug, $settings_hook, $plugin_file, $settings_url, $title, $freemius = null, $repo = 'wp.org' ) {
		$this->plugin_slug   = $plugin_slug;
		$this->settings_hook = $settings_hook;
		$this->plugin_file   = $plugin_file;
		$this->settings_url  = $settings_url;
		$this->title         = $title;
		$this->freemius      = $freemius;
		$this->repo          = $repo;
		$this->hooks();
	}

	/**
	 * @since 1.0
	 */
	private function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'plugins_loaded', array( $this, 'languages' ) );
		add_action( 'init', array( $this, 'set_strings' ) );
		add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
		add_action( 'wp_ajax_pdl_dismiss_notice', array( $this, 'pdl_dismiss_notice' ) );
		add_action( 'wp_ajax_pdl_later_notice', array( $this, 'pdl_later_notice' ) );
		add_action( 'init', array( $this, 'redirect_to_settings' ) );
		add_filter( 'plugin_action_links_' . $this->plugin_file, array( $this, 'settings_link' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_meta' ), 10, 2 );
		register_activation_hook( $this->plugin_file, array( $this, 'plugin_activate' ) );
		register_uninstall_hook(
			$this->plugin_file,
			array(
				'\AlanEFPluginDonation\PluginDonation',
				'plugin_uninstall',
			)
		);
	}

	/**
	 * @since 1.0
	 */
	public static function plugin_uninstall() {
		$x = plugin_basename( __FILE__ );
		do {
			$slug = $x;
			$x    = dirname( $x );
		} while ( ! empty( $x ) && '.' !== $x );
		delete_option( $slug . '_donate' );
		delete_option( $slug . '_review' );
	}

	/**
	 * @since 1.1
	 */
	public function redirect_to_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( get_option( $this->plugin_slug . '-activate', false ) ) {
			delete_option( $this->plugin_slug . '-activate' );
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required WordPress provided and no impact if not present
			if ( ! isset( $_GET['activate-multi'] ) ) {
				wp_safe_redirect( $this->settings_url );
				exit;
			}
		}
	}

	/**
	 * @param $links
	 *
	 * @return array
	 * @since 1.1
	 *
	 */
	public function settings_link( $links ) {
		$settings_link = '<a href="' . esc_url( $this->settings_url ) . '">' . esc_html( $this->get_string( 35 ) ) . '</a>';
		array_unshift(
			$links,
			$settings_link
		);

		return $links;
	}

	private function get_string( $id ) {
		if ( isset( $this->strings[ $id ] ) ) {
			return $this->strings[ $id ];
		}

		return '??';
	}

	function plugin_meta( $links, $file ) {

		if ( $this->plugin_file === $file ) {
			$new_links = array(
				'<a href="https://ko-fi.com/wpalan" target="_blank">' . esc_html( $this->get_string( 34 ) ) . '</a>'
			);

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}

	/**
	 * @since 1.0
	 */
	public function plugin_activate() {
		add_option( $this->plugin_slug . '-activate', true );
		$this->set_timers();
	}

	/**
	 * Sets the timer data for reminders if not already set
	 *
	 * @since 1.1
	 */
	public function set_timers() {
		$donate = get_option( $this->plugin_slug . '_donate', false );
		if ( false === $donate ) {
			add_option( $this->plugin_slug . '_donate', time() );
		}
		$review = get_option( $this->plugin_slug . '_review', false );
		if ( false === $review ) {
			add_option( $this->plugin_slug . '_review', time() );
		}
	}

	/**
	 * @since 1.0
	 */
	public function languages() {
		load_plugin_textdomain(
			'plugin-donation-lib',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/plugindonation_lib/languages'
		);
	}

	/**
	 * @param string $hook page hook provided by WordPress
	 *
	 * @since 1.0
	 */
	public function enqueue_styles( $hook ) {
		if ( $hook === $this->settings_hook ) {
			$this->add_inline_admin_style();

			return;
		}
	}

	/**
	 * Styles for the tab element on the admin display
	 *
	 * @since 1.0
	 */
	private function add_inline_admin_style() {
		$style = <<<EOT
/* Style the tab */
.tab {
  overflow: hidden;
  border: 1px solid #ccc;
  background-color: #f1f1f1;
}

/* Style the buttons that are used to open the tab content */
.tab button {
  background-color: inherit;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
  display: none;
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-top: none;
  flex-wrap: wrap;
  gap: 20px;
  align-items: center;
}
.tabcontent div {
  flex-grow: 1;
}

.tabcontent div:nth-of-type(2) {
  flex-basis: 250px;
}
div.tabcontentwrap div:first-child{
  display: flex;
}
/* other styles */
.pdl_w-full {
  width: 100%;
}
.pdl_max-w-full {
  max-width: 100%;
}
.pdl_md__hide {
  display: none;
}
@media only screen and (max-width: 768px) {
  .pdl_md__hide {
    display: block;
  }
  .pdl_lg__hide {
    display: none;
  }
}
@media only screen and (min-width: 769px) {
  .pdl_md__hide {
    display: none;
  }
  .pdl_lg__hide {
    display: block;
  }
}

EOT;

		wp_add_inline_style( 'admin-bar', $style );
	}

	/**
	 * @param string $hook page hook provided by WordPress
	 *
	 * @since 1.0
	 */
	public function enqueue_scripts( $hook ) {
		if ( $this->admin_page_we_use() ) {
			wp_enqueue_script( 'plugindonation_lib', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), '1.0', false );
			wp_localize_script(
				'plugindonation_lib',
				'pdl_notice',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'plugindonation_lib' ),
				)
			);
		}
	}

	/**
	 * Only on pages we want to be effective on touch
	 *
	 * @return bool
	 *
	 * @since 1.1
	 */
	public function admin_page_we_use() {
		$page             = get_current_screen()->base;
		$display_on_pages = array(
			'dashboard',
			'plugins',
			'tools',
			'options-general',
			$this->settings_hook,
		);

		return in_array( $page, $display_on_pages );
	}

	public function set_strings() {
		$this->strings = array(
			esc_html__( 'Gift a Donation', 'plugin-donation-lib' ),
			// 0
			esc_html__( 'Hi, I\'m Alan and I built this free plugin to solve problems I had, and I hope it solves your problem too.', 'plugin-donation-lib' ),
			// 1
			esc_html__( 'It would really help me know that others find it useful and a great way of doing this is to gift me a small donation', 'plugin-donation-lib' ),
			// 2
			esc_html__( 'Gift a donation: select your desired option', 'plugin-donation-lib' ),
			// 3
			esc_html__( 'My Bitcoin donation wallet', 'plugin-donation-lib' ),
			// 4
			esc_html__( 'Gift a donation via PayPal', 'plugin-donation-lib' ),
			// 5
			esc_html__( 'My Bitcoin Cash address', 'plugin-donation-lib' ),
			// 6
			esc_html__( 'My Ethereum address', 'plugin-donation-lib' ),
			// 7
			esc_html__( 'My Dogecoin address', 'plugin-donation-lib' ),
			// 8
			esc_html__( 'Contribute', 'plugin-donation-lib' ),
			// 9
			esc_html__( 'Contribute to the Open Source Project in other ways', 'plugin-donation-lib' ),
			// 10
			esc_html__( 'Submit a review', 'plugin-donation-lib' ),
			// 11
			esc_html__( 'Translate to your language', 'plugin-donation-lib' ),
			// 12
			esc_html__( 'SUBMIT A REVIEW', 'plugin-donation-lib' ),
			// 13
			esc_html__( 'If you are happy with the plugin then we would love a review. Even if you are not so happy feedback is always useful, but if you have issues we would love you to make a support request first so we can try and help.', 'plugin-donation-lib' ),
			// 14
			esc_html__( 'SUPPORT FORUM', 'plugin-donation-lib' ),
			// 15
			esc_html__( 'Providing some translations for a plugin is very easy and can be done via the WordPress system. You can easily contribute to the community and you don\'t need to translate it all.', 'plugin-donation-lib' ),
			// 16
			esc_html__( 'TRANSLATE INTO YOUR LANGUAGE', 'plugin-donation-lib' ),
			// 17
			esc_html__( 'As an open source project you are welcome to contribute to the development of the software if you can. The development plugin is hosted on GitHub.', 'plugin-donation-lib' ),
			// 18
			esc_html__( 'CONTRIBUTE ON GITHUB', 'plugin-donation-lib' ),
			// 19
			esc_html__( 'Get Support', 'plugin-donation-lib' ),
			// 20
			esc_html__( 'WordPress SUPPORT FORUM', 'plugin-donation-lib' ),
			// 21
			esc_html__( 'Hi I\'m Alan and I support the free plugin', 'plugin-donation-lib' ),
			// 22
			esc_html__( 'for you.  You have been using the plugin for a while now and WordPress has probably been through several updates by now. So I\'m asking if you can help keep this plugin free, by donating a very small amount of cash. If you can that would be a fantastic help to keeping this plugin updated.', 'plugin-donation-lib' ),
			// 23
			esc_html__( 'Donate via this page', 'plugin-donation-lib' ),
			// 24
			esc_html__( 'Remind me later', 'plugin-donation-lib' ),
			// 25
			esc_html__( 'I have already donated', 'plugin-donation-lib' ),
			// 26
			esc_html__( 'I don\'t want to donate, dismiss this notice permanently', 'plugin-donation-lib' ),
			// 27
			esc_html__( 'Hi I\'m Alan and you have been using this plugin', 'plugin-donation-lib' ),
			// 28
			esc_html__( 'for a while - that is awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress? Just to help spread the word and boost my motivation..', 'plugin-donation-lib' ),
			// 29
			esc_html__( 'OK, you deserve it', 'plugin-donation-lib' ),
			// 30
			esc_html__( 'Maybe later', 'plugin-donation-lib' ),
			// 31
			esc_html__( 'Already done', 'plugin-donation-lib' ),
			// 32
			esc_html__( 'No thanks, dismiss this request', 'plugin-donation-lib' ),
			// 33
			esc_html__( 'Donate to Support', 'plugin-donation-lib' ),
			// 34
			esc_html__( 'Settings', 'plugin-donation-lib' ),
			// 35
			esc_html__( 'Help Develop', 'plugin-donation-lib' ),
			// 36
			/**
			 * @since 1.4
			 */
			esc_html__( 'Buy Me a Coffee makes supporting fun and easy. In just a couple of taps, you can donate (buy me a coffee) and leave a message. You don’t even have to create an account!', 'plugin-donation-lib' ),
			// 37
			esc_html__( 'Github ISSUES', 'plugin-donation-lib' ),
			// 38
		);

		$this->strings = apply_filters( 'plugindonation_lib_strings_' . $this->plugin_slug, $this->strings );

	}

	/**
	 * @since 1.0
	 */
	public function display() {
		if ( ( $this->freemius !== null && ! $this->freemius->is_free_plan() ) || $this->is_anti_spam_pro_active() ) {
			return;
		}
		?>
        <div class="pdl_md__hide">
                <a href="https://fullworksplugins.com/products/anti-spam/" target="_blank"><img class=" pdl_w-full pdl_max-w-full" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'images/banners/anti-spam-02.png'; ?>"
                                                                                               alt="anti spam banner" /></a>
            </div>
        <div class="pdl_lg__hide">
            <a href="https://fullworksplugins.com/products/anti-spam/" target="_blank"><img class=" pdl_w-full pdl_max-w-full" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'images/banners/anti-spam-01.png'; ?>"
                                                                                            alt="anti spam banner" /></a>
        </div>

        <tr valign="top">
            <th scope="row"><?php echo esc_html( $this->get_string( 0 ) ); ?></th>
            <td>
                <p>
					<?php echo esc_html( $this->get_string( 1 ) ); ?>
                </p>
                <p>
					<?php echo esc_html( $this->get_string( 2 ) ); ?>
                </p>
                <h3>
					<?php echo esc_html( $this->get_string( 3 ) ); ?>
                </h3>
                <!-- Tab links -->
                <div class="tab">
                    <button class="tablinks" onclick="openPDLTab(event, 'BMAC')"><img height="32"
                                                                                      src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'images/logos/BMAC.svg'; ?>">
                    </button>
                    <button class="tablinks" onclick="openPDLTab(event, 'PP')"><img height="32"
                                                                                    src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'images/logos/PP.png'; ?>">
                    </button>

                </div>

                <!-- Tab content -->
                <div class="tabcontentwrap">
                    <div id="BMAC" class="tabcontent">
                        <div>
                            <a href="https://ko-fi.com/wpalan" target="_blank"><img alt="kofi logo" height="48"
                                                                                    src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'images/logos/BMAC.svg'; ?>"></a>
                        </div>
                        <div>
							<?php echo esc_html( $this->get_string( 37 ) ); ?><br><br> <strong><a
                                        href="https://ko-fi.com/wpalan"
                                        target="_blank">https://ko-fi.com/wpalan</a></strong>
                        </div>
                        <div>
                            <a href="https://ko-fi.com/wpalan" target="_blank"><img height="140" alt="kofi QR code"
                                                                                    src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'images/QRcodes/BMAC.png'; ?>
                                "></a>
                        </div>
                    </div>
                    <div id="PP" class="tabcontent">
                        <div><a href="https://www.paypal.com/donate/?hosted_button_id=UGRBY5CHSD53Q"
                                target="_blank"><img alt="PayPal Logo" height="48"
                                                     src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'images/logos/PP.png'; ?>">
                            </a></div>
                        <div><a href="https://www.paypal.com/donate/?hosted_button_id=UGRBY5CHSD53Q"
                                target="_blank"><?php echo esc_html( $this->get_string( 5 ) ); ?>
                            </a></div>
                        <div><a href="https://www.paypal.com/donate/?hosted_button_id=UGRBY5CHSD53Q"
                                target="_blank"><img height="48" alt="PayPal Cards Logo"
                                                     src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'images/logos/PPcards.png'; ?>">
                            </a></div>
                    </div>

                </div>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php echo esc_html( $this->get_string( 9 ) ); ?></th>
            <td>
                <h3>
					<?php echo esc_html( $this->get_string( 10 ) ); ?>
                </h3>
                <!-- Tab links -->
                <div class="tab">
					<?php
					//  here check repo
					if ( 'wp.org' === $this->repo ) {
					?>
                    <button class="tablinks" onclick="openPDLTab(event, 'review-tab')"><img alt="" height="32"
                                                                                            src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'images/logos/reviews.png'; ?>"><br><?php echo esc_html( $this->get_string( 11 ) ); ?>
                    </button>
                    <button class="tablinks" onclick="openPDLTab(event, 'translate-tab')"><img alt="" height="32"
                                                                                               src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'images/logos/translate.png'; ?>"><br><?php echo esc_html( $this->get_string( 12 ) ); ?>
                    </button>
					<?php
					//  end check repo
					//  here check repo
					if ( 'wp.org' === $this->repo || 'github.org' === $this->repo ) {
						?>
                        <button class="tablinks" onclick="openPDLTab(event, 'github-tab')"><img alt="" height="32"
                                                                                                src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'images/logos/github.png'; ?>"><br><?php echo esc_html( $this->get_string( 36 ) ); ?>
                        </button>
						<?php
						//  end check repo
					}
					?>
                </div>
                <!-- Tab content -->
                <div class="tabcontentwrap">
					<?php
					//  here check repo
					if ( 'wp.org' === $this->repo ) {
						?>
                        <div id="review-tab" class="tabcontent">
                            <div>
                                <a class="button-secondary"
                                   href="https://wordpress.org/support/plugin/<?php echo esc_attr( $this->plugin_slug ); ?>/reviews/?view=all#new-post"
                                   target="_blank"><?php echo esc_html( $this->get_string( 13 ) ); ?></a>
                            </div>
                            <div>
                                <p><?php echo esc_html( $this->get_string( 14 ) ); ?></p>
                            </div>
                            <div>
                                <a class="button-secondary"
                                   href="https://wordpress.org/support/plugin/<?php echo esc_attr( $this->plugin_slug ); ?>/"
                                   target="_blank"><?php echo esc_html( $this->get_string( 15 ) ); ?></a>
                            </div>
                        </div>
                        <div id="translate-tab" class="tabcontent">
                            <div>
                                <a href="https://translate.wordpress.org/projects/wp-plugins/<?php echo esc_attr( $this->plugin_slug ); ?>/"
                                   target="_blank"><img height="48"
                                                        src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'images/logos/translate.png'; ?>">
                                </a></div>
                            <div>
                                <p><?php echo esc_html( $this->get_string( 16 ) ); ?> </p>
                            </div>
                            <div><a class="button-secondary"
                                    href="https://translate.wordpress.org/projects/wp-plugins/<?php echo esc_attr( $this->plugin_slug ); ?>/"
                                    target="_blank"><?php echo esc_html( $this->get_string( 17 ) ); ?></a>
                            </div>
                        </div>
					<?php }
					if ( 'wp.org' === $this->repo || 'github.org' === $this->repo ) {

					} ?>
                    <div id="github-tab" class="tabcontent">
                        <div><a href="https://github.com/alanef/<?php echo esc_attr( $this->plugin_slug ); ?>/"
                                target="_blank"><img height="48"
                                                     src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'images/logos/github.png'; ?>"></a>
                        </div>
                        <div>
                            <p><?php echo esc_html( $this->get_string( 18 ) ); ?></p>
                        </div>
                        <div>
                            <a class="button-secondary"
                               href="https://github.com/alanef/<?php echo esc_attr( $this->plugin_slug ); ?>/"
                               target="_blank"><?php echo esc_html( $this->get_string( 19 ) ); ?></a>
                        </div>
                    </div>
					<?php } ?>

                </div>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php echo esc_html( $this->get_string( 20 ) ); ?></th>
            <td>
				<?php
				//  here check repo
				if ( 'wp.org' === $this->repo ) {
					?>
                    <a class="button-secondary"
                       href="https://wordpress.org/support/plugin/<?php echo esc_attr( $this->plugin_slug ); ?>/"
                       target="_blank"><?php echo esc_html( $this->get_string( 21 ) ); ?></a>
					<?php
					//  end check repo
				}
				//  here check repo
				if ( 'github.com' === $this->repo ) {
					?>
                    <a class="button-secondary"
                       href="https://github.com/alanef/<?php echo esc_attr( $this->plugin_slug ); ?>/issues"
                       target="_blank"><?php echo esc_html( $this->get_string( 38 ) ); ?></a>
					<?php
					//  end check repo
				}
				?>
            </td>
        </tr>
		<?php
	}

	/**
	 * @since 1.0
	 */
	public function display_admin_notice() {
		if ( ( $this->freemius !== null && ! $this->freemius->is_free_plan() ) || $this->is_anti_spam_pro_active() ) {
			return;
		}



		$this->set_timers();
		// Don't display notices to users that can't do anything about it.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}
		// Notices are only displayed on the dashboard, plugins, tools, and settings admin pages.
		if ( ! $this->admin_page_we_use() ) {
			return;
		}
		$user_id       = get_current_user_id();
		$um            = get_user_meta( $user_id, 'pdlib_dismissed_notices', true );
		$notice_donate = $this->plugin_slug . '_pdlib_notice_donate';
		if ( ! isset( $um[ $notice_donate ] ) || true !== $um[ $notice_donate ] ) {
			$donate = get_option( $this->plugin_slug . '_donate' );
			if ( false !== $donate && time() > (int) $donate + ( 6 * WEEK_IN_SECONDS ) ) {
				?>
                <div id="<?php echo esc_attr( $notice_donate ); ?>"
                     class="pdl_notice notice is-dismissible notice-warning">
                    <p>
						<?php
						echo esc_html( $this->get_string( 22 ) ) .
						     ' <strong>' . esc_html( $this->title ) .
						     '</strong> ' . esc_html( $this->get_string( 23 ) );
						?>
                    </p>
                    <p>
                        <a href="<?php echo esc_attr( $this->settings_url ); ?>"><?php echo esc_html( $this->get_string( 24 ) ); ?></a>
                    </p>
                    <p><a class="remind" href=""><?php echo esc_html( $this->get_string( 25 ) ); ?></a></p>
                    <p><a class="dismiss"
                          href=""><?php echo esc_html( $this->get_string( 26 ) ); ?></a></p>
                    <p><a class="dismiss"
                          href=""><?php echo esc_html( $this->get_string( 27 ) ); ?></a>
                    </p>
                </div>
				<?php
			}
		}
		$notice_review = $this->plugin_slug . '_pdlib_notice_review';
		if ( ! isset( $um[ $notice_review ] ) || true !== $um[ $notice_review ] ) {
			$review = get_option( $this->plugin_slug . '_review' );
			if ( false !== $review && time() > (int) $review + ( 4 * WEEK_IN_SECONDS ) ) {
				?>
                <div id="<?php echo esc_attr( $notice_review ); ?>"
                     class="pdl_notice notice is-dismissible notice-sucess">
                    <p>
						<?php
						echo esc_html( $this->get_string( 28 ) ) .
						     ' <strong>' . esc_html( $this->title ) .
						     '</strong> ' . esc_html( $this->get_string( 29 ) );
						?>
                    </p>
                    <p>
                        <a target="_blank"
                           href="https://wordpress.org/support/plugin/<?php echo esc_attr( $this->plugin_slug ); ?>/reviews/?view=all#new-post"><?php echo esc_html( $this->get_string( 30 ) ); ?></a>
                    </p>
                    <p><a class="remind" href=""><?php echo esc_html( $this->get_string( 31 ) ); ?></a></p>
                    <p><a class="dismiss"
                          href=""><?php echo esc_html( $this->get_string( 32 ) ); ?></a></p>
                    <p><a class="dismiss"
                          href=""><?php echo esc_html( $this->get_string( 33 ) ); ?></a>
                    </p>
                </div>
				<?php
			}
		}
	}

    private function is_anti_spam_pro_active() {
	    // check if global $fwantispam_fs
        if ( isset( $GLOBALS['fwantispam_fs'] ) ) {
	        /** @var \Freemius $fwantispam_fs Freemius global object. */
            global $fwantispam_fs;
            if ( !empty( $fwantispam_fs ) ) {
                if ( $fwantispam_fs->can_use_premium_code() ) {
                    return true;
                }
            }
        }
        return false;
    }

	/**
	 * @since 1.0
	 */
	public function pdl_dismiss_notice() {
		if ( ! $this->valid_ajax_call() ) {
			return;
		}
		$user_id = get_current_user_id();
		/* handle issue of old version */
		$slugs = array(
			'stop-user-enumeration',
			'clean-and-simple-contact-form-by-meg-nicholas',
			'redirect-404-error-page-to-homepage-or-custom-page',
			'simple-google-maps-short-code',
			'stop-wp-emails-going-to-spam',
		);
		foreach ( $slugs as $slug ) {
			$legacy = get_user_meta( $user_id, $slug . '_pdlib_dismissed_notices', true );
			if ( ! empty( $legacy ) ) {
				update_user_meta( $user_id, 'pdlib_dismissed_notices', $legacy );
				delete_user_meta( $user_id, $slug . '_pdlib_dismissed_notices' );
				break;
			}
		}
		/* end of tidy up */
		$um = get_user_meta( $user_id, 'pdlib_dismissed_notices', true );
		if ( ! is_array( $um ) ) {
			$um = array();
		}
		// phpcs:ignore WordPress.Security.NonceVerification -- nonce is verified in valid_ajax_call
		$um[ sanitize_text_field( $_POST['id'] ) ] = true;
		update_user_meta( $user_id, 'pdlib_dismissed_notices', $um );
		wp_die();
	}

	/**
	 * Check if doing ajax and capability
	 *
	 * @return bool
	 *
	 * @since 1.1
	 */
	private function valid_ajax_call() {
		if ( ! wp_doing_ajax() ) {
			return false;
		}
		if ( ! current_user_can( 'install_plugins' ) ) {
			return false;
		}
		if ( isset( $POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'plugindonation_lib' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @since 1.0
	 */
	public function pdl_later_notice() {
		if ( ! $this->valid_ajax_call() ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification -- nonce is verified in valid_ajax_call
		if ( sanitize_text_field( $_POST['id'] ) === $this->plugin_slug . '_pdlib_notice_donate' ) {
			// donate later
			$donate = get_option( $this->plugin_slug . '_donate' );
			if ( false !== $donate ) {
				update_option( $this->plugin_slug . '_donate', (int) $donate + ( 6 * WEEK_IN_SECONDS ) );
			}
		}
		// phpcs:ignore WordPress.Security.NonceVerification -- nonce is verified in valid_ajax_call
		if ( sanitize_text_field( $_POST['id'] ) === $this->plugin_slug . '_pdlib_notice_review' ) {
			// review later
			$review = get_option( $this->plugin_slug . '_review' );
			if ( false !== $review ) {
				update_option( $this->plugin_slug . '_review', (int) $review + ( 4 * WEEK_IN_SECONDS ) );
			}
		}
		wp_die();
	}
}
