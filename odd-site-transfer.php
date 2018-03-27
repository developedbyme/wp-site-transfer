<?php
	/*
	Plugin Name: Odd Site transfer
	Plugin URI: http://oddalice.se
	Description: Transferring data between sites
	Version: 3.0.1
	Author: Odd alice
	Author URI: http://oddalice.se
	*/

	define("ODD_SITE_TRANSFER_DOMAIN", "odd-site-transfer");
	define("ODD_SITE_TRANSFER_TEXTDOMAIN", "odd-site-transfer");
	define("ODD_SITE_TRANSFER_MAIN_FILE", __FILE__);
	define("ODD_SITE_TRANSFER_DIR", untrailingslashit( dirname( __FILE__ )  ) );
	define("ODD_SITE_TRANSFER_URL", untrailingslashit( plugins_url('',  __FILE__ )  ) );
	define("ODD_SITE_TRANSFER_VERSION", '3.0.1');

	require_once( ODD_SITE_TRANSFER_DIR . "/libs/OddSiteTransfer/bootstrap.php" );

	$OddSiteTransferPlugin = new \OddSiteTransfer\Plugin();
	\OddSiteTransfer\Plugin::$singleton = $OddSiteTransferPlugin;
	
	
	function ost_debug_transfer_type_page($type, $post_id, $post) {
		switch($post->post_type) {
			case 'page':
				return 'post';
		}
		return null;
	}
	
	add_filter(ODD_SITE_TRANSFER_DOMAIN.'/post_transfer_type', 'ost_debug_transfer_type_page', 10, 3);
?>
