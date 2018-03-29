<?php

	namespace OddSiteTransfer\RestApi\BidirectionalTransfer;
	
	use \WP_Query;
	use \OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\BidirectionalTransfer\RunImportsEndpoint
	class RunImportsEndpoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\BidirectionalTransfer\RunImportsEndpoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\BidirectionalTransfer\RunImportsEndpoint::perform_call<br />");
			
			$plugin = \OddSiteTransfer\Plugin::$singleton;
			
			$ids = $data['ids'];
			
			foreach($ids as $transfer_id) {
				
				$transfer_post_id = ost_get_transfer_post_id($transfer_id);
				
				if($transfer_post_id !== -1) {
					ost_import_transfer($transfer_post_id);
				}
			}
			
			return $this->output_success($transfer_post_id);
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\BidirectionalTransfer\RunImportsEndpoint<br />");
		}
	}
?>