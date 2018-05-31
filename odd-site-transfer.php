<?php
	/*
	Plugin Name: Odd Site transfer
	Plugin URI: http://oddalice.se
	Description: Transferring data between sites
	Version: 3.1.5
	Author: Odd alice
	Author URI: http://oddalice.se
	*/

	define("ODD_SITE_TRANSFER_DOMAIN", "odd-site-transfer");
	define("ODD_SITE_TRANSFER_TEXTDOMAIN", "odd-site-transfer");
	define("ODD_SITE_TRANSFER_MAIN_FILE", __FILE__);
	define("ODD_SITE_TRANSFER_DIR", untrailingslashit( dirname( __FILE__ )  ) );
	define("ODD_SITE_TRANSFER_URL", untrailingslashit( plugins_url('',  __FILE__ )  ) );
	define("ODD_SITE_TRANSFER_VERSION", '3.1.5');

	require_once( ODD_SITE_TRANSFER_DIR . "/libs/OddSiteTransfer/bootstrap.php" );

	$OddSiteTransferPlugin = new \OddSiteTransfer\Plugin();
	\OddSiteTransfer\Plugin::$singleton = $OddSiteTransferPlugin;
	
	require_once( ODD_SITE_TRANSFER_DIR . "/external-functions.php" );
	
	function ost_encoder_setup_wc_order($encoder, $transfer_type, $post_id, $post) {
		if($post->post_type === 'shop_order') {
			return new \OddSiteTransfer\SiteTransfer\Encoders\WcOrderPostEncoder();
		}
		if($post->post_type === 'shop_subscription') {
			return new \OddSiteTransfer\SiteTransfer\Encoders\WcSubscriptionPostEncoder();
		}
		return $encoder;
	}
	
	add_filter(ODD_SITE_TRANSFER_DOMAIN.'/encoder_setup/post', 'ost_encoder_setup_wc_order', 10, 4);
?>
