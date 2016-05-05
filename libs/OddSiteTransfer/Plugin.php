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
			
			$sync_user_end_point = new \OddSiteTransfer\RestApi\SyncUserEndPoint();
			$sync_user_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$sync_user_end_point->setup('sync/user', 'odd-site-transfer', 1, 'POST');
			//METODO: security
			$this->_rest_api_end_points[] = $sync_user_end_point;	
			
			$sync_post_end_point = new \OddSiteTransfer\RestApi\SyncPostEndPoint();
			$sync_post_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$sync_post_end_point->setup('sync/post', 'odd-site-transfer', 1, 'POST');
			//METODO: security
			$this->_rest_api_end_points[] = $sync_post_end_point;
			
			$sync_image_end_point = new \OddSiteTransfer\RestApi\SyncImageEndPoint();
			$sync_image_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$sync_image_end_point->setup('sync/image', 'odd-site-transfer', 1, 'POST');
			//METODO: security
			$this->_rest_api_end_points[] = $sync_image_end_point;
			
			$sync_term_end_point = new \OddSiteTransfer\RestApi\SyncTermEndPoint();
			$sync_term_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$sync_term_end_point->setup('sync/term', 'odd-site-transfer', 1, 'POST');
			//METODO: security
			$this->_rest_api_end_points[] = $sync_term_end_point;
			
			
			$transfer_post_end_point = new \OddSiteTransfer\RestApi\TransferPostEndPoint();
			$transfer_post_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$transfer_post_end_point->setup('post/(?P<id>\d+)/transfer', 'odd-site-transfer', 1, 'GET');
			//METODO: security
			$this->_rest_api_end_points[] = $transfer_post_end_point;
			
		}
		
		
		public function hook_admin_enqueue_scripts() {
			//echo("\OddSiteTransfer\Plugin::hook_admin_enqueue_scripts<br />");
			
			parent::hook_admin_enqueue_scripts();
			
			$screen = get_current_screen();
			
			wp_enqueue_script( 'odd-site-transfer-admin-main', ODD_SITE_TRANSFER_URL . '/assets/js/admin-main.js');
			wp_localize_script(
				'odd-site-transfer-admin-main',
				'oaWpAdminData',
				array(
					'screen' => $screen,
					'restApiBaseUrl' => get_home_url().'/wp-json/'
				)
			);
		}
		
		
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\Plugin<br />");
		}
	}
?>