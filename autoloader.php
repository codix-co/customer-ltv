<?php

// Block direct access to file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cdxltv_autoload( $class_name ) {
	if ( false === strpos( $class_name, 'Codix\\CustomerLTV' ) ) {
		return;
	}

	$file_parts       = explode( '\\', $class_name );
	$file_parts_count = count( $file_parts );

	$file_name = '';
	$namespace = '';
	for ( $i = $file_parts_count - 1; $i > 1; $i-- ) {
		$current = strtolower( $file_parts[ $i ] );
		$current = str_ireplace( '_', '-', $current );

		// If last item in file parts
		if ( $file_parts_count - 1 === $i ) {
			if ( strpos( strtolower( $file_parts[ $file_parts_count - 1 ] ), 'interface' ) ) {
				$interface_name = explode( '_', $file_parts[ $file_parts_count - 1 ] );
				$interface_name = $interface_name[0];

				$file_name = "interface-$interface_name.php";
			} else {
				$file_name = "class-$current.php";
			}
		} else {
			$namespace = '/' . $current . $namespace;
		}
	}

	$filepath  = trailingslashit( dirname( __FILE__ ) . $namespace );
	$filepath .= $file_name;

	if ( file_exists( $filepath ) ) {
		include_once $filepath;
	} else {
		wp_die(
			esc_html( "The file attempting to be loaded at $filepath does not exist." )
		);
	}
}

try {
	spl_autoload_register( 'cdxltv_autoload' );
} catch ( Exception $e ) {
	die( esc_html( $e->getMessage() ) );
}
