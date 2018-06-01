<?php
	namespace OddSiteTransfer;
	
	use \OddSiteTransfer\OddCore\PluginBase;
	
	// \OddSiteTransfer\Plugin
	class Plugin extends PluginBase {
		
		public static $singleton = null;
		
		function __construct() {
			//echo("\OddSiteTransfer\Plugin::__construct<br />");
			
			parent::__construct();
			
			$this->add_css('oddsitetransfer-sync-notice', ODD_SITE_TRANSFER_URL.'/assets/css/admin-style.css');
		}
		
		public function register_hooks() {
			//echo("\OddSiteTransfer\Plugin::register_hooks<br />");
			
			parent::register_hooks();
			
			add_action('admin_footer', array($this, 'hook_admin_footer'), $this->_default_hook_priority);
			
		}
		
		protected function create_filters() {
			//echo("\MRouterData\Plugin::create_filters<br />");
			
			add_filter('wprr/range_query/transfers-to-send', array($this, 'filter_range_query_transfers_to_send'), 10, 2);
			add_filter('wprr/range_encoding/transfer', array($this, 'filter_range_encoding_transfer'), 10, 2);
			
			add_filter('parent_file', array($this, 'filter_parent_file'), 999, 1);
		}
		
		protected function create_pages() {
			//echo("\OddSiteTransfer\Plugin::create_pages<br />");
			
			$current_page = new \OddSiteTransfer\OddCore\Admin\Pages\ReactPage();
			$current_page->set_names('Site transfer', 'Site transfer', 'site_transfer');
			
			$this->add_page($current_page);
		}
		
		public function hook_admin_menu() {
			
			parent::hook_admin_menu();
			
			remove_menu_page('edit.php?post_type=ost_channel');
			remove_menu_page('edit.php?post_type=ost_transfer');
			
			add_submenu_page(
				'site_transfer',
				'Channels',
				'Channels',
				'edit_posts',
				'edit.php?post_type=ost_channel'
			);
			
			add_submenu_page(
				'site_transfer',
				'Transfers',
				'Transfers',
				'edit_posts',
				'edit.php?post_type=ost_transfer'
			);
		}
		
		public function filter_parent_file($parent_file) {
			global $current_screen;
			
			$base = $current_screen->base;
			$post_type = $current_screen->post_type;
			
			if($base === 'edit' && ($post_type === 'ost_channel' || $post_type === 'ost_transfer')) {
				$parent_file = 'site_transfer';
			}
			
			return $parent_file;
		}
		
		protected function create_custom_post_types() {
			//echo("\OddSiteTransfer\Plugin::create_custom_post_types<br />");
			
			/*
			$post_settings = new \OddSiteTransfer\OddCore\Admin\MetaData\ReactPostMetaDataBox();
			$post_settings->create_simple_meta_fields(array('_odd_server_transfer_id'));
			$post_settings->set_name('Site transfer settings');
			$post_settings->set_nonce_name('odd_site_transfer_post_settings');
			$post_settings->set_component('siteTransferPostSettings', array());
			
			$current_post_type = new \OddSiteTransfer\OddCore\Admin\CustomPostTypes\AlreadyRegisteredCustomPostTypePost();
			$current_post_type->set_names('page');
			$current_post_type->add_meta_box_after_title($post_settings);
			$this->add_custom_post_type($current_post_type);
			*/
			
			$this->add_custom_post_type(new \OddSiteTransfer\Admin\CustomPostTypes\ChannelCustomPostType());
			$this->add_custom_post_type(new \OddSiteTransfer\Admin\CustomPostTypes\TransferCustomPostType());
			
		}
		
		protected function create_additional_hooks() {
			//echo("\OddSiteTransfer\Plugin::create_additional_hooks<br />");
			
			$transfer_hooks = new \OddSiteTransfer\Admin\TransferHooks();
			
			$this->external_access['transfer_hooks'] = $transfer_hooks;
			$this->add_additional_hook($transfer_hooks);
		}
		
		protected function create_rest_api_end_points() {
			//echo("\OddSiteTransfer\Plugin::create_rest_api_end_points<br />");
			
			/*
			//v2
			$current_end_point = new \OddSiteTransfer\RestApi\GetTransferInfoEndPoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('info', 'odd-site-transfer', 2, 'GET');
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\CompareImageEndPoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('compare/image', 'odd-site-transfer', 2, 'POST');
			//METODO: security
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\SyncImageEndPoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('sync/image', 'odd-site-transfer', 2, 'POST');
			//METODO: security
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\TransferWithDependency\SyncPostEndPoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('sync/post', 'odd-site-transfer', 2, 'POST');
			//METODO: security
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\TransferWithDependency\SyncPostWithHooksEndPoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('sync/post-with-hooks', 'odd-site-transfer', 2, 'POST');
			//METODO: security
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\TransferWithDependency\SyncTermEndPoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('sync/term', 'odd-site-transfer', 2, 'POST');
			//METODO: security
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\TransferWithDependency\SyncUserEndPoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('sync/user', 'odd-site-transfer', 2, 'POST');
			//METODO: security
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\TransferWithDependency\SyncMissingPostsEndPoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('sync/missing-posts', 'odd-site-transfer', 2, 'POST');
			//METODO: security
			$this->_rest_api_end_points[] = $current_end_point;
			
			
			$current_end_point = new \OddSiteTransfer\RestApi\TransferWithDependency\TransferPostEndPoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('post/(?P<id>\d+)/transfer', 'odd-site-transfer', 2, 'GET');
			//METODO: security
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\TransferWithDependency\RemoveMissingPostsEndPoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('(?P<postType>[a-zA-Z0-9_\-]+)/remove-missing', 'odd-site-transfer', 2, 'GET');
			//METODO: security
			$this->_rest_api_end_points[] = $current_end_point;
			*/
			
			$api_namespace = 'ost';
			$api_version = 3;
			
			//v3
			$current_end_point = new \OddSiteTransfer\RestApi\GetTransferInfoEndPoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('info', $api_namespace, $api_version, 'GET');
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\CompareImageEndPoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('compare/image', $api_namespace, $api_version, 'POST');
			//METODO: security
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\SyncImageEndPoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('incoming-transfer/image', $api_namespace, $api_version, 'POST');
			//METODO: security
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\CreateTransferForPostEndpoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('post/(?P<id>\d+)/create-transfer', $api_namespace, $api_version, 'GET'); //METODO: change to post
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\UpdateTransferForPostEndpoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('post/(?P<id>\d+)/update-transfer', $api_namespace, $api_version, 'GET'); //METODO: change to post
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\BidirectionalTransfer\OutgoingTransferEndpoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('outgoing-transfer/(?P<id>.+)', $api_namespace, $api_version, 'GET'); //METODO: change to post
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\BidirectionalTransfer\IncomingTransferEndpoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('incoming-transfer', $api_namespace, $api_version, 'POST');
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\BidirectionalTransfer\RunImportsEndpoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('run-imports', $api_namespace, $api_version, 'POST');
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\BidirectionalTransfer\ImportTransferEndpoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('import-transfer/(?P<id>.+)', $api_namespace, $api_version, 'GET'); //METODO: change to post
			$this->_rest_api_end_points[] = $current_end_point;
			
			$current_end_point = new \OddSiteTransfer\RestApi\BidirectionalTransfer\TransferDataEndpoint();
			$current_end_point->add_headers(array('Access-Control-Allow-Origin' => '*'));
			$current_end_point->setup('transfer/(?P<id>.+)', $api_namespace, $api_version, 'GET');
			$this->_rest_api_end_points[] = $current_end_point;
			//METODO: add security
			
		}
		
		
		public function hook_admin_enqueue_scripts() {
			//echo("\OddSiteTransfer\Plugin::hook_admin_enqueue_scripts<br />");
			
			parent::hook_admin_enqueue_scripts();
			
			wp_enqueue_script( 'odd-site-transfer-admin-main', ODD_SITE_TRANSFER_URL . '/assets/js/admin.js');
		}
		
		public function hook_admin_footer() {
			if(function_exists('wprr_output_module')) {
				wprr_output_module('globalSync');
			}
			else {
				trigger_error('WPRR API is not active. Can\'t create transfer module.', E_USER_WARNING);
			}
		}
		
		public function filter_range_query_transfers_to_send($query_args, $data) {
			
			$query_args['meta_query'] = array(
				array(
					'key' => 'ost_transfer_status',
					'value' => 0,
					'compare' => '=',
					'type' => 'NUMERIC'
				)
			);
			
			return $query_args;
		}
		
		public function filter_range_encoding_transfer($return_object, $post_id) {
			
			$return_object['transferId'] = get_post_meta($post_id, 'ost_id', true);
			$return_object['hash'] = get_post_meta($post_id, 'ost_encoded_data_hash', true);
			
			return $return_object;
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\Plugin<br />");
		}
	}
?>