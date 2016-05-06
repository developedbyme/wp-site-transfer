<?php
	namespace OddSiteTransfer\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\GetTransferInfoEndPoint
	class GetTransferInfoEndPoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\GetTransferInfoEndPoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\GetTransferInfoEndPoint::perform_call<br />");
			
			$return_data = array();
			
			$return_data['version'] = ODD_SITE_TRANSFER_VERSION;
			
			return $this->output_success($return_data);
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\GetTransferInfoEndPoint<br />");
		}
	}
?>