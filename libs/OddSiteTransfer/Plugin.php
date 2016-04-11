<?php
	namespace OddSiteTransfer;
	
	use \OddSiteTransfer\OddCore\PluginBase;
	
	class Plugin extends PluginBase {
		
		function __construct() {
			//echo("\OddSiteTransfer\Plugin::__construct<br />");
			
			parent::__construct();
		}
		
		protected function create_pages() {
			//echo("\OddSiteTransfer\Plugin::create_pages<br />");
			
		}
		
		protected function create_custom_post_types() {
			//echo("\BtdmParallaxAds\Plugin::create_custom_post_types<br />");
			
			$this->add_custom_post_type(new \OddSiteTransfer\Admin\CustomPostTypes\ServerTransferCustomPostType());
		}
		
		protected function create_additional_hooks() {
			//echo("\OddSiteTransfer\Plugin::create_additional_hooks<br />");
			
			$this->add_additional_hook(new \OddSiteTransfer\Admin\TransferHooks());
		}
		
		protected function create_rest_api_end_points() {
			//echo("\OddSiteTransfer\Plugin::create_rest_api_end_points<br />");
			
			$sync_post_end_point = new \OddSiteTransfer\RestApi\SyncPostEndPoint();
			$sync_post_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$sync_post_end_point->setup('sync/post', 'odd-site-transfer', 1, 'POST');
			//METODO: security
			$this->_rest_api_end_points[] = $sync_post_end_point;	
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\Plugin<br />");
		}
	}
?>