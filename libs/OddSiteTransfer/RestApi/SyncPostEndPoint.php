<?php
	namespace OddSiteTransfer\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\SyncPostEndPoint
	class SyncPostEndPoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\SyncPostEndPoint::__construct<br />");
			
			
		}
		
		protected function update_acf_field($name, $field, $post_id, $repeater_path = NULL, $meta_path = null) {
			//echo("\OddSiteTransfer\RestApi\SyncPostEndPoint::update_acf_field<br />");
			
			switch($field['type']) {
				default:
					echo('Unknown type:'.$field['type']);
				case "textarea":
				case "text":
				case "number":
				case "url":
				case "radio":
				case "post_object":
					if($repeater_path) {
						update_sub_field($repeater_path, $field['value'], $post_id);
					}
					else {
						update_field($name, $field['value'], $post_id);
					}
					break;
				case "repeater":
					if(!$repeater_path) {
						$rows = get_field($name, $post_id);
						if($rows) {
							$row_count = $rows ? count($rows) : 0;
							for($i = 0; $i < $row_count; $i++) {
								//$successful_delete = delete_row($name, $row_count-$i, $post_id);
							}
						}
					}
					
					$new_rows = $field['value'];
					
					foreach($new_rows as $index => $row) {
						//METODO: lenght is set directly to meta data
						//$new_row_index = add_row($name, NULL, $post_id);
						//echo($new_row_index."<br />");
						
						foreach($row as $row_field_name => $row_field) {
							$new_path = array($name, $index+1, $row_field_name);
							$new_meta_path = array($name, $index, $row_field_name);
							if($repeater_path) {
								$new_path = array_merge($repeater_path, array($index+1, $row_field_name));
								$new_meta_path = array_merge($meta_path, array($index, $row_field_name));
							}
							//echo(implode(',', $new_path).'<br />');
							$this->update_acf_field($repeater_path, $row_field, $post_id, $new_path, $new_meta_path);
						}
					}
					
					$meta_value_name = $name;
					if($meta_path) {
						$meta_value_name = implode('_', $meta_path);
					}
					
					update_post_meta($post_id, $meta_value_name, count($new_rows));
					
					break;
			}
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\SyncPostEndPoint::perform_call<br />");
			
			$post_ids = $data['ids'];
			$post_data = $data['data'];
			$meta_data = $data['meta_data'];
			
			$local_id = $post_ids['local_id'];
			
			$new_id = NULL;
			
			remove_all_actions('save_post'); //MEDEBUG
			
			if(!$local_id) {
				$new_id = wp_insert_post($post_data);
			}
			else {
				
				$post_data['ID'] = $local_id;
				
				$new_id = wp_update_post($post_data);
				//echo('.'.$local_id.'--'.$new_id);
				if($new_id == 0) {
					unset($data['ID']);
					$new_id = wp_insert_post($post_data);
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
				if(isset($meta_data['acf'])) {
					foreach($meta_data['acf'] as $name => $field) {
						$this->update_acf_field($name, $field, $new_id);
					}
				}
				
				return $this->output_success($new_id);
			}
			return $this->output_error('Could not create/update post.');
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\SyncPostEndPoint<br />");
		}
	}
?>