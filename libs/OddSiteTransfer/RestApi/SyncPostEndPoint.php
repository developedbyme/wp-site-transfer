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
			
			remove_all_actions('save_post'); //MEDEBUG
			
			if(!$local_id) {
				$new_id = wp_insert_post($data);
			}
			else {
				
				$data['ID'] = $local_id;
				
				$new_id = wp_update_post($data);
				//echo('.'.$local_id.'--'.$new_id);
				if($new_id == 0) {
					unset($data['ID']);
					$new_id = wp_insert_post($data);
				}
				
				if(is_wp_error($new_id)) {
					$error_string = '';
					$errors = $new_id->get_error_messages();
					foreach ($errors as $error) {
						$error_string .= $error;
					}
					return $this->output_error($error_string);
				}
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