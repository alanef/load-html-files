<?php

namespace Load_HTML_Files\Control;

class Uninstall {

	/**
	 *
	 */
	public static function uninstall() {
		// User content (HTML Files posts, their categories and uploads/html_files) is deliberately kept on uninstall.
		delete_option( 'load-html-files-settings' );
		\Fullworks_Free_Plugin_Lib\Main::plugin_uninstall( 'LHF-Free' );
	}

}
