<?php

	namespace OddSiteTransfer\RestApi\BidirectionalTransfer;
	
	use \WP_Query;
	use \OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\BidirectionalTransfer\IncomingTransferEndpoint
	class IncomingTransferEndpoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\BidirectionalTransfer\IncomingTransferEndpoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\BidirectionalTransfer\IncomingTransferEndpoint::perform_call<br />");
			
			$plugin = \OddSiteTransfer\Plugin::$singleton;
			
			$items = $data['items'];
			
			$publish_ids = array();
			
			foreach($items as $item) {
				
				$transfer_id = $item['id'];
				$transfer_post_id = ost_get_transfer_post_id($transfer_id);
				
				if($transfer_post_id === -1) {
					$transfer_post_id = ost_create_transfer($transfer_id, $item['type'], $item['name']);
					$publish_ids[] = $transfer_post_id;
				}
				
				ost_update_transfer($transfer_post_id, $item['data']);
			}
			
			foreach($publish_ids as $publish_id) {
				wp_update_post(array('ID' => $publish_id, 'post_status' => 'publish'));
			}
			
			return $this->output_success($transfer_post_id);
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\BidirectionalTransfer\IncomingTransferEndpoint<br />");
		}
	}
?>