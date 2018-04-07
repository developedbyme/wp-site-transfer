<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	use \WP_Post;
	use \WP_Term;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject
	class PostEncoderBaseObject {
		
		protected $meta_fields = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::__construct<br />");
			
			$this->add_meta_field('_wp_attached_file', 'data');
			$this->add_meta_field('_wp_attachment_metadata', 'data');
			$this->add_meta_field('_wp_attachment_image_alt', 'data');
			
		}
		
		public function add_meta_field($name, $type = 'data') {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::add_meta_field<br />");
			$this->meta_fields[] = array('name' => $name, 'type' => $type);
			
			return $this;
		}
		
		protected function get_post_transfer_id($post) {
			return ost_get_post_transfer_id($post);
		}
		
		protected function add_dependency($type, $id, &$dependencies) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::add_dependency<br />");
			
			$new_dependency = array('type' => $type, 'id' => $id);
			
			$dependencies[] = $new_dependency;
		}
		
		protected function get_referenced_posts($post_ids, &$dependencies) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::get_referenced_posts<br />");
			
			if(is_array($post_ids)) {
				$return_array = array();
				foreach($post_ids as $post_id) {
					if($post_id instanceof WP_Post) {
						$post_id = $post_id->ID;
					}
					$linked_post = get_post($post_id);
					
					if($linked_post) {
						$linked_post_id = $this->get_post_transfer_id($linked_post);
						$this->add_dependency('post', $linked_post_id, $dependencies);
						$return_array[] = $linked_post_id;
					}
				}
				return $return_array;
			}
			else {
				if(empty($post_ids)) {
					return '';
				}
				$post_id = $post_ids;
				if($post_id instanceof WP_Post) {
					$post_id = $post_id->ID;
				}
				$linked_post = get_post($post_id);
				
				if($linked_post) {
					$linked_post_id = $this->get_post_transfer_id($linked_post);
					$this->add_dependency('post', $linked_post_id, $dependencies);
				
					return $linked_post_id;
				}
			}
			
			return null;
		}
		
		protected function get_referenced_terms($term_ids, &$dependencies) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::get_referenced_terms<br />");
			
			if(is_array($term_ids)) {
				$return_array = array();
				foreach($term_ids as $term_id) {
					if($term_id instanceof WP_Term) {
						$term_id = $term_id->term_id;
					}
					$linked_term = get_term($term_id);
					
					if($linked_term) {
						
						$term_transfer_id = ost_get_term_transfer_id($linked_term);
						$return_array[] = $term_transfer_id;
						
						$this->add_dependency('term', $term_transfer_id, $dependencies);
					}
				}
				return $return_array;
			}
			else {
				if(empty($term_ids)) {
					return '';
				}
				$term_id = $term_ids;
				if($term_id instanceof WP_Term) {
					$term_id = $term_id->term_id;
				}
				$linked_term = get_term($term_id);
				
				if($linked_term) {
					$term_transfer_id = ost_get_term_transfer_id($linked_term);
					$this->add_dependency('term', $term_transfer_id, $dependencies);
				
					return $term_transfer_id;
				}
			}
			
			return null;
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
			//MENOTE: using the modified makes the hash function break, ignoring for now
			//$return_object['data']['post_modified'] = $object->post_modified;
			//$return_object['data']['post_modified_gmt'] = $object->post_modified_gmt;
			$return_object['data']['comment_status'] = $object->comment_status;
			$return_object['data']['menu_order'] = $object->menu_order;
			$return_object['data']['post_mime_type'] = $object->post_mime_type;
			
			$parent_id = $object->post_parent;
			
			if($parent_id != 0) {
				$linked_post = get_post($parent_id);
				$linked_post_id = $this->get_post_transfer_id($linked_post);
				$this->add_dependency('post', $linked_post_id, $return_object['dependencies']);
				$return_object['parent'] = $linked_post_id;
			}
			else {
				$return_object['parent'] = null;
			}
			
			
			$author_id = $object->post_author;
			
			if($author_id != 0) {
				$post_author = get_user_by('id', $author_id);
				$post_author_transfer_id = ost_get_user_transfer_id($post_author);
				
				$this->add_dependency('user', $post_author_transfer_id, $return_object['dependencies']);
				$return_object['author'] = $post_author_transfer_id;
			}
		}
		
		protected function encode_featured_image($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_featured_image<br />");
			
			$media_post_id = get_post_thumbnail_id($object->ID);
			
			if($media_post_id) {
				$media_post = get_post($media_post_id);
				
				$local_thumbnail_id = $this->get_post_transfer_id($media_post);
				$this->add_dependency('post', $local_thumbnail_id, $return_object['dependencies']);
				$return_object['meta_data']['post_thumbnail_id'] = $local_thumbnail_id;
			}
		}
		
		protected function encode_meta_data($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_meta_data<br />");
			
			$post_id = $object->ID;
			
			if(!isset($return_object['meta_data'])) $return_object['meta_data'] = array();
			if(!isset($return_object['meta_data']['meta'])) $return_object['meta_data']['meta'] = array();
			if(!isset($return_object['meta_data']['meta_posts'])) $return_object['meta_data']['meta_posts'] = array();
			
			$this->encode_featured_image($object, $return_object);
			
			foreach($this->meta_fields as $meta_field) {
				
				$meta_field_name = $meta_field['name'];
				//var_dump($meta_field_name);
				
				$meta_field_data = get_post_meta($post_id, $meta_field_name, true);
				switch($meta_field['type']) {
					case 'post_ids':
						$linked_post_ids = $this->get_referenced_posts($meta_field_data, $return_object['dependencies']);
						$return_object['meta_data']['meta_posts'][$meta_field_name] = $linked_post_ids;
						break;
					default:
						//METODO: add warning
					case 'data':
						$return_object['meta_data']['meta'][$meta_field_name] = $meta_field_data;
						break;
				}
			}
		}
		
		protected function encode_taxonomies($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_taxonomies<br />");
			
			global $wp_taxonomies;
			
			if(!isset($return_object['taxonomies'])) $return_object['taxonomies'] = array();
			
			$post_id = $object->ID;
			$taxonomies = array();
			
			$object_type = get_post_type($post_id);
			
			foreach($wp_taxonomies as $name => $taxonomy_object) {
				if(in_array($object_type, $taxonomy_object->object_type)) {
					$taxonomies[] = $name;
				}
			}
			
			foreach($taxonomies as $taxonomy) {
				$current_terms = get_the_terms($post_id, $taxonomy);
				$local_term_ids = array();
				
				if($current_terms) {
					foreach($current_terms as $current_term) {
						$term_transfer_id = ost_get_term_transfer_id($current_term);
						$local_term_ids[] = $term_transfer_id;
						$return_object['dependencies'][] = array('type' => 'term', 'id' => $term_transfer_id);
					}
				}
				else {
					$local_term_ids[] = "";
				}
				
				
				$return_object['taxonomies'][$taxonomy] = $local_term_ids;
				
			}
		}
		
		public function encode_parts($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_parts<br />");
			
			$this->encode_id($object, $return_object);
			$this->encode_content($object, $return_object);
			$this->encode_meta_data($object, $return_object);
			$this->encode_taxonomies($object, $return_object);
			
		}
		
		public function encode($object, $transfer_type) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode<br />");
			
			$return_data = array();
			$return_data['dependencies'] = array();
			
			$this->encode_parts($object, $return_data);
			
			$return_data = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/encode_post/'.$transfer_type, $return_data, $object, $transfer_type);
			
			return $return_data;
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject<br />");
		}
	}
?>