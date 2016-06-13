<?php
	namespace OddSiteTransfer\RestApi\TransferWithDependency;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\TransferWithDependency\RemoveMissingPostsEndPoint
	class RemoveMissingPostsEndPoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\RemoveMissingPostsEndPoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\RemoveMissingPostsEndPoint::perform_call<br />");
			
			$post_type = $data['postType'];
			
			$plugin = \OddSiteTransfer\Plugin::$singleton;
			
			$result_data = $plugin->external_access['transfer_hooks']->remove_missing_posts($post_type);
			
			return $this->output_success($result_data);
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\TransferWithDependency\RemoveMissingPostsEndPoint<br />");
		}
	}
?>