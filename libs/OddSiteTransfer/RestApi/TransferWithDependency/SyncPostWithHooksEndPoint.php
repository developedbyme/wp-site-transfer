<?php
	namespace OddSiteTransfer\RestApi\TransferWithDependency;
	
	use \WP_Query;
	use \OddSiteTransfer\RestApi\TransferWithDependency\SyncPostEndPoint as SyncPostEndPoint;
	
	// \OddSiteTransfer\RestApi\TransferWithDependency\SyncPostWithHooksEndPoint
	class SyncPostWithHooksEndPoint extends SyncPostEndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\SyncPostWithHooksEndPoint::__construct<br />");
			
			
		}
		
		protected function _disable_save_hooks($post_type) {
			//MENOTE: do not disable hooks
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\TransferWithDependency\SyncPostWithHooksEndPoint<br />");
		}
	}
?>