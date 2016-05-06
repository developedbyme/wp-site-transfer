<?php
	namespace OddSiteTransfer\OddCore\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\OddCore\RestApi\IdentifyPostEndPoint
	class IdentifyPostEndPoint extends EndPoint {
		
		protected $_arguments = array();
		
		function __construct() {
			//echo("\OddCore\RestApi\IdentifyPostEndPoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddCore\RestApi\IdentifyPostEndPoint::perform_call<br />");
			
			$post_type = $data['postType'];
			$serach_type = $data['postType'];
			$identifier = $data['id'];
			
			
			
			return $this->output_success($data);
		}
		
		public static function test_import() {
			echo("Imported \OddCore\RestApi\IdentifyPostEndPoint<br />");
		}
	}
?>