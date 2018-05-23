<?php

	namespace OddSiteTransfer\RestApi\BidirectionalTransfer;
	
	use \WP_Query;
	use \OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\BidirectionalTransfer\TransferDataEndpoint
	class TransferDataEndpoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\BidirectionalTransfer\TransferDataEndpoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\BidirectionalTransfer\TransferDataEndpoint::perform_call<br />");
			
			$transfer_id = $data['id'];
			
			$transfer_post_id = ost_get_transfer_post_id($transfer_id);
			
			if($transfer_post_id > 0) {
				$this->output_success($transfer_post_id);
			}
			
			return $this->output_error('No transfer');
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\BidirectionalTransfer\TransferDataEndpoint<br />");
		}
	}
?>