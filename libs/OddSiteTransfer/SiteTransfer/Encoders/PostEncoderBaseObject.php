<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	use \WP_Post;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject
	class PostEncoderBaseObject {
		
		protected $qualifier = null;
		
		protected $meta_fields = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::__construct<br />");
			
			
		}
		
		public function set_qualifier($qualifier) {
			$this->qualifier = $qualifier;
			
			return $this;
		}
		
		public function add_meta_field($name, $type = 'data') {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::add_meta_field<br />");
			$this->meta_fields[] = array('name' => $name, 'type' => $type);
			
			return $this;
		}
		
		public function qualify($object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::qualify<br />");
			
			if($object instanceof WP_Post) {
				return $this->qualifier->qualify($object);
			}
			return false;
		}
		
		protected function get_post_transfer_id($object) {
			$id = get_post_meta($object->ID, '_odd_server_transfer_id', true);
			if(!$id) {
				$id = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
				update_post_meta($object->ID, '_odd_server_transfer_id', $id);
			}
			
			return $id;
		}
		
		protected function add_dependency($type, $id, $additional_info, &$dependencies) {
			echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::add_dependency<br />");
			
			$new_dependency = array('type' => $type, 'id' => $id);
			
			foreach($additional_info as $key => $value) {
				$new_dependency[$key] = $value;
			}
			
			$dependencies[] = $new_dependency;
		}
		
		protected function encode_id($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_id<br />");
			
			$return_object['id'] = $this->get_post_transfer_id($object);
		}
		
		protected function encode_content($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_content<br />");
			
			if(!isset($return_object['data'])) $return_object['data'] = array();
			
			$return_object['data']['post_type'] = $object->post_type;
			$return_object['data']['post_status'] = $object->post_status;
			$return_object['data']['post_title'] = $object->post_title;
			$return_object['data']['post_content'] = $object->post_content;
			$return_object['data']['post_name'] = $object->post_name;
			$return_object['data']['post_date'] = $object->post_date;
			$return_object['data']['post_date_gmt'] = $object->post_date_gmt;
			$return_object['data']['post_modified'] = $object->post_modified;
			$return_object['data']['post_modified_gmt'] = $object->post_modified_gmt;
			$return_object['data']['comment_status'] = $object->comment_status;
			$return_object['data']['menu_order'] = $object->menu_order;
			$return_object['data']['post_mime_type'] = $object->post_mime_type;
			
			
			$author_id = $object->post_author;
			
			if($author_id != 0) {
				$post_author = get_user_by('id', $author_id);
				
				$author_local_id = $post_author->user_login;
				
				$this->add_dependency('user', $author_local_id, array(), $return_object['dependencies']);
				$return_object['author'] = $post_author->user_login;
			}
		}
		
		protected function encode_featured_image($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_featured_image<br />");
			
			$media_post_id = get_post_thumbnail_id($object->ID);
			
			if($media_post_id) {
				$media_post = get_post($media_post_id);
				
				$local_thumbnail_id = $this->get_post_transfer_id($media_post);
				$this->add_dependency('post', $local_thumbnail_id, array('post_type' => $media_post->post_type), $return_object['dependencies']);
				$return_object['meta_data']['post_thumbnail_id'] = $local_thumbnail_id;
			}
		}
		
		protected function encode_meta_data($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_meta_data<br />");
			
			$post_id = $object->ID;
			
			if(!isset($return_object['meta_data'])) $return_object['meta_data'] = array();
			if(!isset($return_object['meta_data']['meta'])) $return_object['meta_data']['meta'] = array();
			
			$this->encode_featured_image($object, $return_object);
			
			foreach($this->meta_fields as $meta_field) {
				
				$meta_field_name = $meta_field['name'];
				//var_dump($meta_field_name);
				
				$meta_field_data = get_post_meta($post_id, $meta_field_name, true);
				switch($meta_field['type']) {
					case 'post_id':
						//METODO
						break;
					case 'post_ids':
						//METODO
						break;
					default:
						//METODO: add warning
					case 'data':
						//MENOTE: do nothing
						break;
				}
				
				$return_object['meta_data']['meta'][$meta_field_name] = $meta_field_data;
			}
		}
		
		protected function encode_taxonomies($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_taxonomies<br />");
			
			if(!isset($return_object['taxonomies'])) $return_object['taxonomies'] = array();
			
			$post_id = $object->ID;
			$taxonomies = array_keys(get_the_taxonomies($post_id));
			
			foreach($taxonomies as $taxonomy) {
				$current_terms = get_the_terms($post_id, $taxonomy);
				$local_term_ids = array();
				
				foreach($current_terms as $current_term) {
					$local_term_ids[] = $current_term->slug;
					$return_object['dependencies'][] = array('type' => 'term', 'id' => $current_term->slug, 'taxonomy' => $taxonomy);
				}
				
				$return_object['taxonomies'][$taxonomy] = $local_term_ids;
				
			}
		}
		
		public function encode_parts($object, &$return_object) {
			echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_parts<br />");
			
			//METODO
			$this->encode_id($object, $return_object);
			$this->encode_content($object, $return_object);
			$this->encode_meta_data($object, $return_object);
			$this->encode_taxonomies($object, $return_object);
		}
		
		public function encode($object) {
			echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode<br />");
			
			$return_data = array();
			$return_data['dependencies'] = array();
			
			$this->encode_parts($object, $return_data);
			
			return $return_data;
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject<br />");
		}
	}
?>