<?php
	namespace OddSiteTransfer\RestApi\TransferWithDependency;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\TransferWithDependency\SyncPostEndPoint
	class SyncPostEndPoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\SyncPostEndPoint::__construct<br />");
			
			
		}
		
		protected function update_acf_field($name, $field, $post_id, $resolved_dependencies, $repeater_path = NULL, $meta_path = null) {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\SyncPostEndPoint::update_acf_field<br />");
			//var_dump($field);
			
			if(!isset($field['value'])) return; //METODO: check that this is correct
			
			switch($field['type']) {
				default:
					echo('Unknown type:'.$field['type']);
				case "textarea":
				case "text":
				case "number":
				case "url":
				case "radio":
				case "wysiwyg":
				case "true_false":
				case "select":
				case "oembed":
					if($repeater_path) {
						update_sub_field($repeater_path, $field['value'], $post_id);
						update_post_meta($post_id, implode('_', $meta_path), $field['value']);
					}
					else {
						update_field($name, $field['value'], $post_id);
					}
					break;
				case "post_object":
				case "image":
				case "relationship":
					$resolved_ids = $this->get_resolved_post_ids($field['value'], $resolved_dependencies);
					if($repeater_path) {
						update_sub_field($repeater_path, $resolved_ids, $post_id);
						update_post_meta($post_id, implode('_', $meta_path), $resolved_ids);
					}
					else {
						update_field($name, $resolved_ids, $post_id);
					}
					break;
				case "taxonomy":
					$ids = $field['value']['ids'];
					$taxonomy = $field['value']['taxonomy'];
					
					if(is_array($ids)) {
						$resolved_ids = $this->get_resolved_term_dependencies($ids, $taxonomy, $resolved_dependencies);
					}
					else {
						$resolved_ids = null;
						if(!empty($ids)) {
							$term = $this->get_resolved_dependency('term_'.$taxonomy, $ids, $resolved_dependencies);
							if($term) {
								$resolved_ids = intval($term->term_id);
							}
						}
					}
					
					if($repeater_path) {
						update_sub_field($repeater_path, $resolved_ids, $post_id);
						update_post_meta($post_id, implode('_', $meta_path), $resolved_ids);
					}
					else {
						update_field($name, $resolved_ids, $post_id);
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
							$this->update_acf_field($repeater_path, $row_field, $post_id, $resolved_dependencies, $new_path, $new_meta_path);
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
		
		public function filter_wp_kses_allowed_html($allowed_post_tags) {
			
			if(!isset($allowed_post_tags['span'])) {
				$allowed_post_tags['span'] = array();
			}
			$span_array = $allowed_post_tags['span'];
			$span_array['data-related-ingredients'] = true;
			$allowed_post_tags['span'] = $span_array;
		
			return $allowed_post_tags;
	
		}
		
		protected function get_post_by_transfer_id($post_type, $id) {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\SyncPostEndPoint::perform_call<br />");
			//var_dump($id);
			
			remove_all_actions('pre_get_posts');
			
			if($post_type === 'any') {
				$post_type = get_post_types(array(), 'names');
			}
			
			$args = array(
				'post_type' => $post_type,
				'post_status' => array('any', 'trash'),
				'meta_key'     => '_odd_server_transfer_id',
				'meta_value'   => $id,
				'meta_compare' => '='
			);
			$query = new WP_Query( $args );
			
			//var_dump($query);
			//var_dump($query->have_posts());
			
			if($query->have_posts()) {
				//METODO: warn for more than 1 match
				return $query->get_posts()[0];
			}
			
			return null;
		}
		
		
		protected function get_resolved_post_ids($ids, $resolved_dependencies) {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\SyncPostEndPoint::get_resolved_post_ids<br />");
			//var_dump($ids);
			
			if(is_array($ids)) {
				$return_array = array();
				
				foreach($ids as $id) {
					$current_post = $this->get_resolved_dependency('post', $id, $resolved_dependencies);
					if($current_post) {
						$return_array[] = $current_post->ID;
					}
				}
				
				return $return_array;
			}
			if(empty($ids)) {
				return '';
			}
			$current_post = $this->get_resolved_dependency('post', $ids, $resolved_dependencies);
			if($current_post) {
				return $current_post->ID;
			}
			return null;
		}
		
		protected function get_dependency($dependency_data, &$return_array, &$missing_dependencies) {
			$id = $dependency_data['id'];
			$type = $dependency_data['type'];
			
			switch($type) {
				case "post":
					$post = $this->get_post_by_transfer_id($dependency_data['post_type'], $id);
					if($post) {
						if($dependency_data['post_type'] === 'attachment') {
							
							$file_name = get_post_meta($post->ID, '_wp_attached_file', true);
							$base_file = wp_upload_dir()['basedir'].'/'.$file_name;
							
							if($file_name === '' || !file_exists($base_file)) {
								$missing_dependencies[] = $dependency_data;
							}
						}
						
						$return_array[$type.'_'.$id] = $post;
					}
					else {
						$missing_dependencies[] = $dependency_data;
					}
					break;
				case "term":
					$taxonomy = $dependency_data['taxonomy'];
					$term = get_term_by('slug', $id, $dependency_data['taxonomy']);
					if($term) {
						$return_array[$type.'_'.$taxonomy.'_'.$id] = $term;
					}
					else {
						$missing_dependencies[] = $dependency_data;
					}
					break;
				case "user":
					$user = get_user_by('login', $id);
					if($user) {
						$return_array[$type.'_'.$id] = $user;
					}
					else {
						$missing_dependencies[] = $dependency_data;
					}
					break;
				default:
					//METODO: error report
					$missing_dependencies[] = $dependency_data;
			}
		}
		
		protected function get_resolved_dependency($type, $id, $resolved_dependencies) {
			$full_id = $type.'_'.$id;
			if(isset($resolved_dependencies[$full_id])) {
				return $resolved_dependencies[$full_id];
			}
			return null;
		}
		
		protected function get_resolved_term_dependencies($ids, $taxonomy, $resolved_dependencies) {
			$return_array = array();
			
			foreach($ids as $id) {
				if($id !== '') {
					$term = $this->get_resolved_dependency('term_'.$taxonomy, $id, $resolved_dependencies);
					if($term) {
						$return_array[] = intval($term->term_id);
					}
				}
			}
			
			return $return_array;
		}
		
		protected function _disable_save_hooks($post_type) {
			remove_all_actions("save_post_{$post_type}");
			remove_all_actions('save_post');
			remove_all_actions('wp_insert_post');
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\SyncPostEndPoint::perform_call<br />");
			
			add_filter('wp_kses_allowed_html', array($this, 'filter_wp_kses_allowed_html'), 10, 1);
			
			//var_dump($data);
			
			$status = $data['status'];
			$transfer_id = $data['id'];
			
			if($status === "non-existing") {
				
				$return_object = array('transferId' => $transfer_id, 'missingDependencies' => array());
				
				$existing_post = $this->get_post_by_transfer_id('any', $transfer_id);
				
				if($existing_post) {
					$return_object['removedId'] = $existing_post->ID;
					$remove_status = wp_trash_post($existing_post->ID);
					$return_object['removedPost'] = $remove_status;
				}
				
				return $this->output_success($return_object);
			}
			
			
			
			//echo('---------');
			//var_dump($transfer_id);
			
			$dependencies = $data['dependencies'];
			
			$resolved_dependencies = array();
			$missing_dependencies = array();
			
			if($dependencies) {
				foreach($dependencies as $dependency) {
					//var_dump($dependency);
				
					$this->get_dependency($dependency, $resolved_dependencies, $missing_dependencies);
				}
			}
			
			//var_dump($resolved_dependencies);
			//var_dump($missing_dependencies);
			
			
			
			$post_data = $data['data'];
			$meta_data = $data['meta_data'];
			$taxonomies = $data['taxonomies'];
			
			$post_type = $post_data['post_type'];
			
			$existing_post = $this->get_post_by_transfer_id($post_type, $transfer_id);
			
			$author_id = $data['author'];
			$author = $this->get_resolved_dependency('user', $author_id, $resolved_dependencies);
			if($author) {
				$post_data['post_author'] = $author->ID;
			}
			
			$new_id = NULL;
			
			$this->_disable_save_hooks($post_type);
			
			if($existing_post) {
				$post_data['ID'] = $existing_post->ID;
				$new_id = wp_update_post($post_data);
			}
			else {
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
			
			if($post_data['post_type'] === 'attachment') {
				
				$base_file = wp_upload_dir()['basedir'].'/'.$meta_data['meta']['_wp_attached_file'];
				$attachment_metadata = $meta_data['meta']['_wp_attachment_metadata'];
				
				if(isset($attachment_metadata['sizes'])) {
					foreach($meta_data['meta']['_wp_attachment_metadata']['sizes'] as $image_size) {
						//METODO: check if it has size before resizing
						$resize_result = image_make_intermediate_size($base_file, $image_size['width'], $image_size['height'], true);
						//METODO: check that resize worked
					}
				}
			}
			
			if(isset($meta_data['post_thumbnail_id'])) {
				//METODO: test removal of thumbnail
				
				$image_post = $this->get_resolved_dependency('post', $meta_data['post_thumbnail_id'], $resolved_dependencies);
				
				//METODO: test this
				//var_dump($image_post);
				if($image_post) {
					set_post_thumbnail($new_id, $image_post->ID);
				}
			}
			
			if(isset($taxonomies)) {
				foreach($taxonomies as $taxonomy => $term_ids) {
					$int_term_ids = $this->get_resolved_term_dependencies($term_ids, $taxonomy, $resolved_dependencies);
					wp_set_object_terms($new_id, $int_term_ids, $taxonomy, false);
				}
				
			}
			
			if(isset($meta_data['acf'])) {
				\OddSiteTransfer\OddCore\Utils\AcfFunctions::ensure_post_has_fields(get_post($new_id));
				
				foreach($meta_data['acf'] as $name => $field) {
					$this->update_acf_field($name, $field, $new_id, $resolved_dependencies);
				}
			}
			
			if(isset($meta_data['meta'])) {
				foreach($meta_data['meta'] as $key => $value) {
					update_post_meta($new_id, $key, $value);
				}
			}
			
			if(isset($meta_data['meta_posts'])) {
				foreach($meta_data['meta_posts'] as $key => $value) {
					update_post_meta($new_id, $key, $this->get_resolved_post_ids($value, $resolved_dependencies));
				}
			}
			
			update_post_meta($new_id, '_odd_server_transfer_id', $transfer_id);
			update_post_meta($new_id, '_odd_server_transfer_is_incoming', 1);
			update_post_meta($new_id, '_odd_server_transfer_incoming_sync_date', date(DATE_ATOM));
			
			return $this->output_success(array('url' => get_permalink($new_id), 'missingDependencies' => $missing_dependencies));
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\TransferWithDependency\SyncPostEndPoint<br />");
		}
	}
?>