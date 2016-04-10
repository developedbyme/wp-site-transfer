<?php 
	/*
	Plugin Name: Odd Site transfer
	Plugin URI: http://oddalice.se
	Description: Transferring data between sites
	Version: 1.0
	Author: Odd alice
	Author URI: http://oddalice.se
	*/
	
	define("OA_SITE_TRANSFER_TEXTDOMAIN", "odd-site-transfer");
	define("OA_SITE_TRANSFER_MAIN_FILE", __FILE__);
	define("OA_SITE_TRANSFER_DIR", untrailingslashit( dirname( __FILE__ )  ) );
	
	require_once( OA_SITE_TRANSFER_DIR . "/libs/OddSiteTransfer/bootstrap.php" );
	
	$OddSiteTransferPlugin = new \OddSiteTransfer\Plugin();
?>