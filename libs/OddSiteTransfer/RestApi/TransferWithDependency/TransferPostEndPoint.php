<?php
	namespace OddSiteTransfer\RestApi\TransferWithDependency;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\TransferWithDependency\TransferPostEndPoint
	class TransferPostEndPoint extends EndPoint {
		
		protected $http_log = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\TransferPostEndPoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\TransferPostEndPoint::perform_call<br />");
			
			$plugin = \OddSiteTransfer\Plugin::$singleton;
			
			$post_id = $data['id'];
			$post = get_post($post_id);
			
			$force = ($data['force'] === '1');
			
			$sync_index = intval(get_post_meta($post_id, '_odd_server_transfer_sync_index', true));
			$sync_index_target = intval(get_post_meta($post_id, '_odd_server_transfer_sync_index_target', true));
			
			if(!$force && ($sync_index_target === $sync_index)) {
				return $this->output_success(array('target' => $sync_index_target, 'index' => $sync_index));
			}
			
			$plugin->external_access['transfer_hooks']->transfer_post($post);
			
			$sync_index = min($sync_index+1, $sync_index_target);
			
			update_post_meta($post_id, '_odd_server_transfer_sync_index', $sync_index);
			
			return $this->output_success(array('target' => $sync_index_target, 'index' => $sync_index, 'httpLog' => $this->http_log));
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\TransferWithDependency\TransferPostEndPoint<br />");
		}
	}
?>