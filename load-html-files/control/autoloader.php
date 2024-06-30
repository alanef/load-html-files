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

/**
 * Autoloader based on tutorial by
 * Tom McFarlin https://tommcfarlin.com/  Licensed    GPL-2.0+
 */

spl_autoload_register( 'load_html_files_autoload' );

function load_html_files_autoload( $class_name ) {


	// If the specified $class_name does not include our namespace, duck out.
	if ( false === strpos( $class_name, 'Load_HTML_Files' ) ) {
		return;
	}

	// Split the class name into an array to read the namespace and class.
	$file_parts = explode( '\\', $class_name );

	// Do a reverse loop through $file_parts to build the path to the file.
	$namespace = '';
	for ( $i = count( $file_parts ) - 1; $i > 0; $i -- ) {

		// Read the current component of the file part.
		$current = strtolower( $file_parts[ $i ] );
		$current = str_ireplace( '_', '-', $current );

		// If we're at the first entry, then we're at the filename.
		if ( count( $file_parts ) - 1 === $i ) {

			/* If 'interface' is contained in the parts of the file name, then
			 * define the $file_name differently so that it's properly loaded.
			 * Otherwise, just set the $file_name equal to that of the class
			 * filename structure.
			 */
			if ( strpos( strtolower( $file_parts[ count( $file_parts ) - 1 ] ), 'interface' ) ) {

				// Grab the name of the interface from its qualified name.
				$interface_name = explode( '_', $file_parts[ count( $file_parts ) - 1 ] );
				$interface_name = $interface_name[0];

				$file_name = "interface-$interface_name.php";

			} else {
				$file_name = "class-$current.php";
			}
		} else {
			$namespace = '/' . $current . $namespace;
		}
	}

	// Now build a path to the file using mapping to the file location.
	$filepath = trailingslashit( dirname( dirname( __FILE__ ) ) . $namespace );
	$filepath .= $file_name;

	// If the file exists in the specified path, then include it.
	if ( file_exists( $filepath ) ) {
		include_once( $filepath );
	} else {
		/* translators: %1$s: full path of missing file */
		wp_die( sprintf( esc_html__( 'The system file attempting to be loaded at %1$s does not exist.', 'load-html-files' ), esc_html($filepath ) ) );
	}
}