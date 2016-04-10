<?php

function OddSiteTransfer_Autoloader( $class ) {
	//echo("OddSiteTransfer_Autoloader<br />");
	
	$namespace_length = strlen("OddSiteTransfer");
	
	// Is a OddSiteTransfer class
	if ( substr( $class, 0, $namespace_length ) != "OddSiteTransfer" ) {
		return false;
	}

	// Uses namespace
	if ( substr( $class, 0, $namespace_length+1 ) == "OddSiteTransfer\\" ) {

		$path = explode( "\\", $class );
		unset( $path[0] );

		$class_file = trailingslashit( dirname( __FILE__ ) ) . implode( "/", $path ) . ".php";

	}

	// Doesn't use namespaces
	elseIf ( substr( $class, 0, $namespace_length+1 ) == "OddSiteTransfer_" ) {

		$path = explode( "_", $class );
		unset( $path[0] );

		$class_file = trailingslashit( dirname( __FILE__ ) ) . implode( "/", $path ) . ".php";

	}

	// Get class
	if ( isset($class_file) && is_file( $class_file ) ) {

		require_once( $class_file );
		return true;

	}

	// Fallback to error
	return false;

}

spl_autoload_register("OddSiteTransfer_Autoloader"); // Register autoloader