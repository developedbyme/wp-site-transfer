<?php

	namespace OddSiteTransfer\RestApi\BidirectionalTransfer;
	
	use \WP_Query;
	use \OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\BidirectionalTransfer\OutgoingTransferEndpoint
	class OutgoingTransferEndpoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\BidirectionalTransfer\OutgoingTransferEndpoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\BidirectionalTransfer\OutgoingTransferEndpoint::perform_call<br />");
			
			$plugin = \OddSiteTransfer\Plugin::$singleton;
			
			$transfer_id = $data['id'];
			$transfer_post_id = ost_get_transfer_post_id($transfer_id);
			
			if($transfer_post_id === -1) {
				return $this->output_error("No transfer for id ".$transfer_id);
			}
			
			$log = $plugin->external_access['transfer_hooks']->send_outgoing_transfer($transfer_post_id);
			
			return $this->output_success(array('log' => $log));
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\BidirectionalTransfer\OutgoingTransferEndpoint<br />");
		}
	}
?>