<?php

namespace Load_HTML_Files\Control;

class Deactivator {

	/**
	 *
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'load_html_files_cron' );
	}
}