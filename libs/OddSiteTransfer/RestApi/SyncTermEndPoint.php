<?php
	namespace OddSiteTransfer\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\SyncTermEndPoint
	class SyncTermEndPoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\SyncTermEndPoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\SyncTermEndPoint::perform_call<br />");
			
			$term_ids = $data['ids'];
			
			$data = $data['data'];
			$taxonomy = $data['taxonomy'];
			
			$local_id = $term_ids['local_id'];
			
			if(!$local_id) {
				
				$existing_term = get_term_by('slug', $data['slug'], $taxonomy);
				
				if($existing_term) {
					$new_id = $existing_term->term_id;
					
					//METODO: update term
				}
				else {
					$result = wp_insert_term($data['name'], $taxonomy, $data);
					
					if(is_wp_error($result)) {
						$error_string = '';
						$errors = $result->get_error_messages();
						foreach ($errors as $error) {
							$error_string .= $error;
						}
						return $this->output_error($error_string);
					}
				
					$new_id = $result['term_id'];
				}
				
				
			}
			else {
				
				$result = wp_update_term($local_id, $taxonomy, $data);
				
				if(is_wp_error($result)) {
					$result = wp_insert_term($data['name'], $taxonomy, $data);
					
					if(is_wp_error($result)) {
						$error_string = '';
						$errors = $result->get_error_messages();
						foreach ($errors as $error) {
							$error_string .= $error;
						}
						return $this->output_error($error_string);
					}
				}
				
				$new_id = $result['term_id'];
				
			}
			
			if($new_id) {
				return $this->output_success($new_id);
			}
			return $this->output_error('Could not create/update term.');
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\SyncTermEndPoint<br />");
		}
	}
?>