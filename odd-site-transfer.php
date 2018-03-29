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
	
	require_once( ODD_SITE_TRANSFER_DIR . "/external-functions.php" );
	
	function ost_debug_transfer_type_page($type, $post_id, $post) {
		if($type === null) {
			switch($post->post_type) {
				case 'page':
					return 'post';
				case 'attachment':
					return 'media';
			}
		}
		return $type;
	}
	
	add_filter(ODD_SITE_TRANSFER_DOMAIN.'/post_transfer_type', 'ost_debug_transfer_type_page', 10, 3);
	
	function ost_debug_transfer_update_type_page($type, $post_id, $post) {
		if($type === null) {
			switch($post->post_type) {
				case 'page':
					return 'always';
			}
		}
		return $type;
	}
	
	add_filter(ODD_SITE_TRANSFER_DOMAIN.'/post_transfer_update_type', 'ost_debug_transfer_update_type_page', 10, 3);
	
	function ost_debug_import_post($transfer_id, $data) {
		echo('ost_debug_import_post');
		
		$post_importer = new \OddSiteTransfer\Admin\PostImporter();
		
		$post_importer->import($transfer_id, $data);
	}
	add_filter(ODD_SITE_TRANSFER_DOMAIN.'/import_post', 'ost_debug_import_post', 10, 2);
	add_filter(ODD_SITE_TRANSFER_DOMAIN.'/import_media', 'ost_debug_import_post', 10, 2);
	
	function ost_debug_post_dependency($transfer_id, $post_id, $post) {
		if($transfer_id === null) {
			switch($post->post_type) {
				case 'page':
				case 'post':
				case 'attachment':
					$transfer_id = ost_get_post_transfer_id($post);
					ost_add_post_transfer($transfer_id, 'post', $post);
					break;
			}
		}
		return $transfer_id;
	}
	
	add_filter(ODD_SITE_TRANSFER_DOMAIN.'/outgoing_transfer/post_dependency', 'ost_debug_post_dependency', 10, 3);
?>
