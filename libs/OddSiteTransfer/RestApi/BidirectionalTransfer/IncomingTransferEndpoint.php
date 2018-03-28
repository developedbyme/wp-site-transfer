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
			
			if($transfer_post_id === -1) {
				return $this->output_error("No transfer for id ".$transfer_id);
			}
			
			return $this->output_success("METODO");
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\BidirectionalTransfer\IncomingTransferEndpoint<br />");
		}
	}
?>