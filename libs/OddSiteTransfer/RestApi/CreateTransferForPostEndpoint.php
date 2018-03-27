<?php
	namespace OddSiteTransfer\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\CreateTransferForPostEndpoint
	class CreateTransferForPostEndpoint extends EndPoint {
		
		protected $http_log = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\CreateTransferForPostEndpoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\CreateTransferForPostEndpoint::perform_call<br />");
			
			//$plugin = \OddSiteTransfer\Plugin::$singleton;
			
			$post_id = $data['id'];
			$post = get_post($post_id);
			
			
			
			return $this->output_success();
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\CreateTransferForPostEndpoint<br />");
		}
	}
?>