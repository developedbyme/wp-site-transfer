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
			
			$transfer_id = $data['id'];
			$transfer_post_id = ost_get_transfer_post_id($transfer_id);
			
			$publish_ids = array();
			
			if($transfer_post_id === -1) {
				//METODO: create transfer item
				$transfer_post_id = ost_create_transfer($transfer_id, $data['type'], $data['name']);
				$publish_ids[] = $transfer_post_id;
			}
			
			ost_update_transfer($transfer_post_id, $data['data']);
			
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