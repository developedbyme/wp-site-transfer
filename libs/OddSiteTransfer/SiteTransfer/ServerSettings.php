<?php
	namespace OddSiteTransfer\SiteTransfer;
	
	use \WP_Query;
	
	use \OddSiteTransfer\OddCore\Utils\HttpLoading as HttpLoading;
	
	// \OddSiteTransfer\SiteTransfer\ServerSettings
	class ServerSettings {
		
		protected $post_encoders = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::__construct<br />");
			
			
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
				//METODO: warn for more than 1 match
				return $query->get_posts()[0];
			}
			
			return null;
		}
		
		protected function compare_image($file_path, $file_size, $server_transfer_post) {
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'compare/image';
			
			$send_data = array('path' => $file_path, 'size' => $file_size);
			
			$repsonse_data = HttpLoading::send_request($url, $send_data);
			$result_object = json_decode($repsonse_data['data'], true);
			
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
			
			if(!$image_exists) {
			
				$send_data = array('path' => $file_path, 'file' => new \CURLFile($file_to_load, 'image/jpeg'));
			
				$repsonse_data = HttpLoading::send_request_with_file($url, $send_data);
				if($repsonse_data) {
					//METODO: check that the image is sent
				}
			}
		}
		
		protected function transfer_dependencies($dependencies, $server_transfer_post) {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::transfer_dependencies<br />");
			
			foreach($dependencies as $dependency) {
				$type = $dependency['type'];
				$id = $dependency['id'];
				
				switch($type) {
					case 'post':
						$post = $this->get_post_by_transfer_id($dependency['post_type'], $id);
						if($post) {
							$this->transfer_post($post, $server_transfer_post);
						}
						break;
					case 'user':
						break;
					case 'term':
						$term = get_term_by('slug', $id, $dependency['taxonomy']);
						if($term) {
							$this->transfer_term($term, $server_transfer_post);
						}
						break;
					default:
						//METODO: error logging
				}
			}
		}
		
		public function transfer_post($post, $server_transfer_post) {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::transfer_post<br />");
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'sync/post';
			
			foreach($this->post_encoders as $encoder) {
				if($encoder->qualify($post)) {
					$encoded_data = $encoder->encode($post);
					//var_dump($encoded_data);
					
					if($post->post_type === 'attachment') {
						$this->transfer_media($post, $server_transfer_post);
					}
					
					$result_data = HttpLoading::send_request($url, $encoded_data);
					//var_dump($result_data);
					//var_dump($result_data['data']);
					$result_object = json_decode($result_data['data'], true);
					//var_dump($result_object);
					
					if($result_object['code'] === 'success') {
						$missing_dependencies = $result_object['data']['missingDependencies'];
						//var_dump($missing_dependencies);
						
						if(count($missing_dependencies) > 0) {
							$this->transfer_dependencies($missing_dependencies, $server_transfer_post);
							$result_data = HttpLoading::send_request($url, $encoded_data);
							$result_object = json_decode($result_data['data'], true);
						}
					}
				}
			}
			
			return false; //MEDEBUG
		}
		
		public function transfer_term($term, $server_transfer_post) {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::transfer_term<br />");
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'sync/term';
			
			foreach($this->post_encoders as $encoder) {
				if($encoder->qualify($term)) {
					$encoded_data = $encoder->encode($term);
					//var_dump($encoded_data);
					
					$result_data = HttpLoading::send_request($url, $encoded_data);
					//var_dump($result_data);
					//var_dump($result_data['data']);
					$result_object = json_decode($result_data['data'], true);
					//var_dump($result_object);
					
					if($result_object['code'] === 'success') {
						$missing_dependencies = $result_object['data']['missingDependencies'];
						
						if(count($missing_dependencies) > 0) {
							$this->transfer_dependencies($missing_dependencies, $server_transfer_post);
							$result_data = HttpLoading::send_request($url, $encoded_data);
							$result_object = json_decode($result_data['data'], true);
						}
					}
				}
			}
			
			return false; //MEDEBUG
		}
		
		public function transfer_user($user, $server_transfer_post) {
			echo("\OddSiteTransfer\SiteTransfer\ServerSettings::transfer_user<br />");
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'sync/user';
			
			foreach($this->post_encoders as $encoder) {
				if($encoder->qualify($term)) {
					$encoded_data = $encoder->encode($term);
					//var_dump($encoded_data);
					
					$result_data = HttpLoading::send_request($url, $encoded_data);
					//var_dump($result_data);
					//var_dump($result_data['data']);
					$result_object = json_decode($result_data['data'], true);
					//var_dump($result_object);
					
					if($result_object['code'] === 'success') {
						$missing_dependencies = $result_object['data']['missingDependencies'];
						
						if(count($missing_dependencies) > 0) {
							$this->transfer_dependencies($missing_dependencies, $server_transfer_post);
							$result_data = HttpLoading::send_request($url, $encoded_data);
							$result_object = json_decode($result_data['data'], true);
						}
					}
				}
			}
			
			return false; //MEDEBUG
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\ServerSettings<br />");
		}
	}
?>