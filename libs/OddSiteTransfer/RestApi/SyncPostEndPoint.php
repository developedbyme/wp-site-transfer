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
			
			$post_ids = $data['ids'];
			$data = $data['data'];
			
			$local_id = $post_ids['local_id'];
			
			$new_id = NULL;
			
			if(!$local_id) {
				
				remove_all_actions('save_post'); //MEDEBUG
				
				$new_id = wp_insert_post($data);
			}
			else {
				
			}
			
			if($new_id) {
				return $this->output_success($new_id);
			}
			return $this->output_error('Could not create/update post.');
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\SyncPostEndPoint<br />");
		}
	}
?>