<?php
/**
 * @copyright (c) 2019.
 * @author            Alan Fuller (support@fullworks)
 * @licence           GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link                  https://fullworks.net
 *
 * This file is part of Fullworks Security.
 *
 *     Fullworks Security is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     Fullworks Security is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with   Fullworks Security.  https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 *
 */

namespace Load_HTML_Files\Control;


use Load_HTML_Files\Admin\Admin_Settings;
use Load_HTML_Files\Admin\Admin;
use Load_HTML_Files\Core\Core;
use Load_HTML_Files\Data\Cpt;

class Plugin {

	public function __construct() {
	}

	/**
	 *
	 */
	public function run() {
		$this->hooks_admin();
		$this->hooks_core();
		$this->hooks_data();
	}

	private function hooks_admin() {
        // phpcs:ignore WordPress.WP.CronInterval -- Verified sniff not detecting custom cron interval.
		add_filter( 'cron_schedules', array( Admin::get_instance(), 'add_cron_schedule' ) );
		add_action( 'admin_enqueue_scripts', array( Admin::get_instance(), 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( Admin::get_instance(), 'enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( Admin::get_instance(), 'add_html_editor' ) );

		add_action( 'admin_menu', array( Admin_Settings::get_instance(), 'settings_setup' ) );
	}

	public function hooks_core() {
		// add cron job.
		add_action( 'wp', array( Core::get_instance(), 'load_html_files_cron' ) );
		// set cron job.
		add_action( 'load_html_files_cron', array( Core::get_instance(), 'load_html_files' ) );
	}

	public function hooks_data() {
		add_action( 'init', array( Cpt::get_instance(), 'add_cpt' ) );
		add_action( 'init', array( Cpt::get_instance(), 'add_taxonomy' ) );
	}

}
