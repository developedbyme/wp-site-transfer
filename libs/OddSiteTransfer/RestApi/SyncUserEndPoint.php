<?php
	namespace OddSiteTransfer\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\SyncUserEndPoint
	class SyncUserEndPoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\SyncUserEndPoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\SyncUserEndPoint::perform_call<br />");
			
			
			$user_ids = $data['ids'];
			$data = $data['data'];
			
			/*
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
			*/
			
			return $this->output_success(array('ids' => $user_ids, 'data' => $data));
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\SyncUserEndPoint<br />");
		}
	}
?>