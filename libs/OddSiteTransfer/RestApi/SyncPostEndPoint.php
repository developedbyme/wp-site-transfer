<?php
	namespace OddSiteTransfer\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\SyncPostEndPoint
	class SyncPostEndPoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\SyncPostEndPoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\SyncPostEndPoint::perform_call<br />");
			
			$data = $data["data"];
			
			return $this->output_success($data);
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\SyncPostEndPoint<br />");
		}
	}
?>