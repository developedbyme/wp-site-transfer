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
			
			if(!isset($field['value'])) return; //METODO: check that this is correct
			
			switch($field['type']) {
				default:
					echo('Unknown type:'.$field['type']);
				case "textarea":
				case "text":
				case "number":
				case "url":
				case "radio":
				case "post_object":
				case "wysiwyg":
					if($repeater_path) {
						update_sub_field($repeater_path, $field['value'], $post_id);
						update_post_meta($post_id, implode('_', $meta_path), $field['value']);
					}
					else {
						update_field($name, $field['value'], $post_id);
					}
					break;
				case "repeater":
					
					$new_rows = $field['value'];
					
					$acf_key = acf_get_field($name)["key"];
					
					foreach($new_rows as $index => $row) {
						//METODO: length is set directly to meta data
						
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
					if($acf_key) {
						update_post_meta($post_id, '_'.$meta_value_name, $acf_key);
					}
					
					break;
			}
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\SyncPostEndPoint::perform_call<br />");
			
			$post_ids = $data['ids'];
			$post_data = $data['data'];
			$meta_data = $data['meta_data'];
			$taxonomies = $data['taxonomies'];
			
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
				
				if(isset($taxonomies)) {
					foreach($taxonomies as $taxonomy => $term_ids) {
						$int_term_ids = array_map('intval', $term_ids);
						wp_set_object_terms($new_id, $int_term_ids, $taxonomy, false);
					}
					
				}
				
				if(isset($meta_data['acf'])) {
					foreach($meta_data['acf'] as $name => $field) {
						$this->update_acf_field($name, $field, $new_id);
					}
				}
				
				if(isset($meta_data['meta'])) {
					foreach($meta_data['meta'] as $key => $value) {
						update_post_meta($new_id, $key, $value);
					}
				}
				
				
				if(isset($meta_data['post_thumbnail_id'])) {
					//METODO: test removal of thumbnail
					set_post_thumbnail($new_id, $meta_data['post_thumbnail_id']);
				}
				
				if($post_data['post_type'] === 'attachment') {
					
					$base_file = wp_upload_dir()['basedir'].'/'.$meta_data['meta']['_wp_attached_file'];
					
					foreach($meta_data['meta']['_wp_attachment_metadata']['sizes'] as $image_size) {
						//METODO: check if it has size before resizing
						$resize_result = image_make_intermediate_size($base_file, $image_size['width'], $image_size['height'], true);
						//METODO: check that resize worked
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