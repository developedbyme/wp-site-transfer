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
			
			$local_id = $user_ids['local_id'];
			
			if(!$local_id) {
				$existing_user = get_user_by('login', $data['user_login']);
				if($existing_user) {
					$data['ID'] = $existing_user->ID;
					$new_id = wp_update_user($data);
				}
				else {
					$new_id = wp_insert_user($data);
					if(is_wp_error($new_id)) {
						$error_string = '';
						$errors = $new_id->get_error_messages();
						foreach ($errors as $error) {
							$error_string .= $error;
						}
						return $this->output_error($error_string);
					}
				}
			}
			else {
				
				$data['ID'] = $local_id;
				
				$new_id = wp_update_user($data);
				//echo('.'.$local_id.'--'.$new_id);
				if($new_id == 0) {
					unset($data['ID']);
					$new_id = wp_insert_user($data);
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
			echo("Imported \OddSiteTransfer\RestApi\SyncUserEndPoint<br />");
		}
	}
?>