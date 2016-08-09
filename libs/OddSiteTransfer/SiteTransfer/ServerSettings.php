<?php
	namespace OddSiteTransfer\SiteTransfer;
	
	use \WP_Query;
	
	use \OddSiteTransfer\OddCore\Utils\HttpLoading as HttpLoading;
	
	// \OddSiteTransfer\SiteTransfer\ServerSettings
	class ServerSettings {
		
		protected $post_encoders = array();
		
		protected $log = array();
		protected $http_log = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::__construct<br />");
			
			
		}
		
		public function has_error() {
			
			foreach($this->log as $log_item) {
				if($log_item['type'] === 'error') {
					return true;
				}
			}
			
			return false;
		}
		
		public function get_result() {
			return array('log' => $this->log, 'http_log' => $this->http_log);
		}
		
		protected function add_log_item($type, $message) {
			$this->log[] = array('type' => $type, 'message' => $message);
		}
		
		public function add_encoder($encoder) {
			$this->post_encoders[] = $encoder;
			
			return $this;
		}
		
		public function qualify_post($post) {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::qualify_post<br />");
			
			foreach($this->post_encoders as $encoder) {
				if($encoder->qualify($post)) {
					return true;
				}
			}
			
			return false;
		}
		
		protected function get_post_by_transfer_id($post_type, $id) {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::get_post_by_transfer_id<br />");
			//var_dump($post_type);
			//var_dump($id);
			
			$args = array(
				'post_type' => $post_type,
				'post_status' => 'any',
				'meta_key'     => '_odd_server_transfer_id',
				'meta_value'   => $id,
				'meta_compare' => '='
			);
			$query = new WP_Query( $args );
			
			//var_dump($query->have_posts());
			if($query->have_posts()) {
				if($query->post_count > 1) {
					$this->add_log_item('warning', 'There are multiple posts with the transfer id '.$id.'. ()');
				}
				return $query->get_posts()[0];
			}
			
			return null;
		}
		
		protected function compare_image($file_path, $file_size, $server_transfer_post) {
			
			$this->add_log_item('log', 'Comparing image '.($file_path).' (size: '.($file_size).')');
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'compare/image';
			
			$send_data = array('path' => $file_path, 'size' => $file_size);
			
			$repsonse_data = HttpLoading::send_request($url, $send_data);
			$this->http_log[] = $repsonse_data;
			$result_object = json_decode($repsonse_data['data'], true);
			
			if($result_object['data']['match']) {
				$this->add_log_item('result', 'Image '.($file_path).' is matching. (size: '.($file_size).')');
			}
			else {
				$this->add_log_item('result', 'Image '.($file_path).' doesn\'t match. (size: '.($file_size).')');
			}
			
			return $result_object['data']['match'];
		}
		
		protected function transfer_media($media, $server_transfer_post) {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::transfer_media<br />");
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'sync/image';
			
			$file_path = get_post_meta($media->ID, '_wp_attached_file', true);
			
			$file_to_load = wp_upload_dir()['basedir'].'/'.$file_path;
			
			$image_exists = $this->compare_image($file_path, filesize($file_to_load), $server_transfer_post);
			
			$image_is_ok = true;
			
			if(!$image_exists) {
				
				$this->add_log_item('log', 'Transfer media '.($file_path).' ()');
				
				$send_data = array('path' => $file_path, 'file' => new \CURLFile($file_to_load, 'image/jpeg'));
			
				$repsonse_data = HttpLoading::send_request_with_file($url, $send_data);
				$this->http_log[] = $repsonse_data;
				
				if($repsonse_data && $repsonse_data['code'] === 200) {
					$result_object = json_decode($repsonse_data['data'], true);
					
					if($result_object && $result_object['code'] === 'success') {
						$this->add_log_item('result', 'Transfered media '.($file_path).' ()');
					}
					else {
						$this->add_log_item('error', 'Error occured while transferring media '.($file_path).' ()');
					}
					
				}
				else {
					$this->add_log_item('error', 'Couldn\'t transfer media '.($file_path).' ()');
					$image_is_ok = false;
				}
			}
			
			return $image_is_ok;
		}
		
		protected function transfer_dependencies($dependencies, $server_transfer_post, $force_dependencies_transfer_steps = 0) {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::transfer_dependencies<br />");
			//$this->add_log_item('log', 'Transfer dependencies '.json_encode($dependencies));
			
			foreach($dependencies as $dependency) {
				$type = $dependency['type'];
				$id = $dependency['id'];
				
				switch($type) {
					case 'post':
						$post = $this->get_post_by_transfer_id($dependency['post_type'], $id);
						if($post) {
							$this->transfer_post($post, $server_transfer_post, $force_dependencies_transfer_steps-1);
						}
						else {
							$this->add_log_item('error', 'Can\'t resolve post '.$id.' of type '.$dependency['post_type']);
						}
						break;
					case 'user':
						$term = get_user_by('login', $id);
						if($term) {
							$this->transfer_user($term, $server_transfer_post, $force_dependencies_transfer_steps-1);
						}
						else {
							$this->add_log_item('error', 'Can\'t resolve user '.$id);
						}
						break;
					case 'term':
						$term = get_term_by('slug', $id, $dependency['taxonomy']);
						if($term) {
							$this->transfer_term($term, $server_transfer_post, $force_dependencies_transfer_steps-1);
						}
						else {
							$this->add_log_item('error', 'Can\'t resolve term '.$id.' in taxonomy '.$dependency['taxonomy']);
						}
						break;
					default:
						$this->add_log_item('error', 'Unknown dependency type '.($type).'. ()');
						break;
				}
			}
		}
		
		public function transfer_post($post, $server_transfer_post, $force_dependencies_transfer_steps = 0) {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::transfer_post<br />");
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'sync/post';
			$url_with_hooks = $base_url.'sync/post-with-hooks';
			
			$has_error = false;
			$return_value = null;
			
			foreach($this->post_encoders as $encoder) {
				if($encoder->qualify($post)) {
					$encoded_data = $encoder->encode($post);
					$this->add_log_item('log', 'Transfer post '.($post->post_title).' (type: '.($post->post_type).', id: '.($post->ID).', forced dependency steps: '.$force_dependencies_transfer_steps.')');
					//var_dump($encoded_data);
					
					if($post->post_type === 'attachment') {
						$media_is_ok = $this->transfer_media($post, $server_transfer_post);
						if(!$media_is_ok) {
							$this->add_log_item('error', 'Cancelled transfer of post '.($post->post_title).', as media didn\'t transfer. (type: '.($post->post_type).', id: '.($post->ID).', forced dependency steps: '.$force_dependencies_transfer_steps.')');
						}
					}
					
					if($force_dependencies_transfer_steps > 0) {
						$this->transfer_dependencies($encoded_data['dependencies'], $server_transfer_post, $force_dependencies_transfer_steps);
					}
					
					$result_data = HttpLoading::send_request($url, $encoded_data);
					$this->http_log[] = $result_data;
					//echo('---------------------');
					//var_dump($result_data);
					//var_dump($result_data['data']);
					$result_object = json_decode($result_data['data'], true);
					//var_dump($result_object);
					
					if($result_object['code'] === 'success') {
						$this->add_log_item('result', 'Sent post '.($post->post_title).' (type: '.($post->post_type).', id: '.($post->ID).', forced dependency steps: '.$force_dependencies_transfer_steps.')');
						$missing_dependencies = $result_object['data']['missingDependencies'];
						//var_dump($missing_dependencies);
						
						if(count($missing_dependencies) > 0) {
							$this->add_log_item('log', 'Re-transfer post with missing dependencies '.($post->post_title).' (type: '.($post->post_type).', id: '.($post->ID).', forced dependency steps: '.$force_dependencies_transfer_steps.')');
							
							$this->transfer_dependencies($missing_dependencies, $server_transfer_post);
							$result_data = HttpLoading::send_request($url, $encoded_data);
							$this->http_log[] = $result_data;
							$result_object = json_decode($result_data['data'], true);
							if($result_object['code'] === 'success') {
								$this->add_log_item('result', 'Sent post '.($post->post_title).' after dependencies. (type: '.($post->post_type).', id: '.($post->ID).', forced dependency steps: '.$force_dependencies_transfer_steps.')');
								$return_value = array('transfer_type' => $encoded_data['status']);
							}
							else {
								$has_error = true;
								
								$this->add_log_item('error', 'Error occured when re-transfering post '.($post->post_title).'. (type: '.($post->post_type).', id: '.($post->ID).', forced dependency steps: '.$force_dependencies_transfer_steps.')');
							}
						}
						else {
							$return_value = array('transfer_type' => $encoded_data['status']);
						}
						
						if(isset($result_object['data']['url'])) {
							$return_value['url'] = $result_object['data']['url'];
						}
						
						if(!$has_error) {
							$this->add_log_item('log', 'Re-transfer post with hooks '.($post->post_title).' (type: '.($post->post_type).', id: '.($post->ID).', forced dependency steps: '.$force_dependencies_transfer_steps.')');
							
							$result_data = HttpLoading::send_request($url_with_hooks, $encoded_data);
							$this->http_log[] = $result_data;
							$result_object = json_decode($result_data['data'], true);
							if($result_object['code'] === 'success') {
								$this->add_log_item('result', 'Sent post '.($post->post_title).' with hooks. (type: '.($post->post_type).', id: '.($post->ID).', forced dependency steps: '.$force_dependencies_transfer_steps.')');
							}
							else {
								$this->add_log_item('warning', 'Could not trigger hooks for '.($post->post_title).'. (type: '.($post->post_type).', id: '.($post->ID).', forced dependency steps: '.$force_dependencies_transfer_steps.')');
							}
						}
					}
					else {
						$has_error = true;
						
						$this->add_log_item('error', 'Error occured when transfering post '.($post->post_title).'. (type: '.($post->post_type).', id: '.($post->ID).', forced dependency steps: '.$force_dependencies_transfer_steps.')');
					}
					break;
				}
			}
			
			return $return_value;
		}
		
		public function transfer_term($term, $server_transfer_post, $force_dependencies_transfer_steps = 0) {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::transfer_term<br />");
			
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'sync/term';
			
			foreach($this->post_encoders as $encoder) {
				if($encoder->qualify($term)) {
					$this->add_log_item('log', 'Transfer term '.($term->name).' in taxonomy '.($term->taxonomy).'. (forced dependency steps: '.$force_dependencies_transfer_steps.')');
					
					$encoded_data = $encoder->encode($term);
					//var_dump($encoded_data);
					
					if($force_dependencies_transfer_steps > 0) {
						$this->transfer_dependencies($encoded_data['dependencies'], $server_transfer_post, $force_dependencies_transfer_steps);
					}
					
					$result_data = HttpLoading::send_request($url, $encoded_data);
					$this->http_log[] = $result_data;
					//var_dump($result_data);
					//var_dump($result_data['data']);
					$result_object = json_decode($result_data['data'], true);
					//var_dump($result_object);
					
					if($result_object['code'] === 'success') {
						$this->add_log_item('result', 'Sent term '.($term->name).' in taxonomy '.($term->taxonomy).'. (forced dependency steps: '.$force_dependencies_transfer_steps.')');
						$missing_dependencies = $result_object['data']['missingDependencies'];
						
						if(count($missing_dependencies) > 0) {
							$this->transfer_dependencies($missing_dependencies, $server_transfer_post);
							$result_data = HttpLoading::send_request($url, $encoded_data);
							$this->http_log[] = $result_data;
							$result_object = json_decode($result_data['data'], true);
						}
					}
					break;
				}
			}
			
			return false; //MEDEBUG
		}
		
		public function transfer_user($user, $server_transfer_post, $force_dependencies_transfer_steps = 0) {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::transfer_user<br />");
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'sync/user';
			
			foreach($this->post_encoders as $encoder) {
				if($encoder->qualify($user)) {
					$this->add_log_item('log', 'Transfer user '.($user->name).' (id: '.($user->ID).', forced dependency steps: '.$force_dependencies_transfer_steps.')');
					$encoded_data = $encoder->encode($user);
					//var_dump($encoded_data);
					
					if($force_dependencies_transfer_steps > 0) {
						$this->transfer_dependencies($encoded_data['dependencies'], $server_transfer_post, $force_dependencies_transfer_steps);
					}
					
					$result_data = HttpLoading::send_request($url, $encoded_data);
					$this->http_log[] = $result_data;
					//var_dump($result_data);
					//var_dump($result_data['data']);
					$result_object = json_decode($result_data['data'], true);
					//var_dump($result_object);
					
					if($result_object['code'] === 'success') {
						$this->add_log_item('result', 'Sent user '.($user->name).' (id: '.($user->ID).', forced dependency steps: '.$force_dependencies_transfer_steps.')');
						$missing_dependencies = $result_object['data']['missingDependencies'];
						
						if(count($missing_dependencies) > 0) {
							$this->transfer_dependencies($missing_dependencies, $server_transfer_post);
							$result_data = HttpLoading::send_request($url, $encoded_data);
							$this->http_log[] = $result_data;
							$result_object = json_decode($result_data['data'], true);
						}
					}
					else {
						$this->add_log_item('error', 'Error occured when transfering user '.($user->name).'. (id: '.($user->ID).', forced dependency steps: '.$force_dependencies_transfer_steps.')');
					}
					break;
				}
			}
			
			return false; //MEDEBUG
		}
		
		public function transfer_missing_posts($post_type, $existing_post_ids, $server_transfer_post) {
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			$encoded_data = array('post_type' => $post_type, 'existing_ids' => $existing_post_ids);
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'sync/missing-posts';
			
			$result_data = HttpLoading::send_request($url, $encoded_data);
			$this->http_log[] = $result_data;
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\ServerSettings<br />");
		}
	}
?>