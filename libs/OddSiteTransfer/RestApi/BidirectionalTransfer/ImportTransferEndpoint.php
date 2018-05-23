<?php

	namespace OddSiteTransfer\RestApi\BidirectionalTransfer;
	
	use \WP_Query;
	use \OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\BidirectionalTransfer\ImportTransferEndpoint
	class ImportTransferEndpoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\BidirectionalTransfer\ImportTransferEndpoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\BidirectionalTransfer\ImportTransferEndpoint::perform_call<br />");
			
			$transfer_id = $data['id'];
			
			$transfer_post_id = ost_get_transfer_post_id($transfer_id);
			
			if($transfer_post_id !== -1) {
				ost_import_transfer($transfer_post_id);
			}
			
			return $this->output_success($transfer_post_id);
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\BidirectionalTransfer\ImportTransferEndpoint<br />");
		}
	}
?>