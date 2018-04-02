<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	use \WP_Term;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\TermEncoderBaseObject
	class TermEncoderBaseObject {
		
		protected $qualifier = null;
		
		protected $meta_fields = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\TermEncoderBaseObject::__construct<br />");
			
			
		}
		
		public function set_qualifier($qualifier) {
			$this->qualifier = $qualifier;
			
			return $this;
		}
		
		public function add_meta_field($name, $type = 'data') {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\TermEncoderBaseObject::add_meta_field<br />");
			$this->meta_fields[] = array('name' => $name, 'type' => $type);
			
			return $this;
		}
		
		public function qualify($object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\TermEncoderBaseObject::qualify<br />");
			
			if($object instanceof WP_Term) {
				return $this->qualifier->qualify($object);
			}
			return false;
		}
		
		protected function add_dependency($type, $id, $additional_info, &$dependencies) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\TermEncoderBaseObject::add_dependency<br />");
			
			$new_dependency = array('type' => $type, 'id' => $id);
			
			foreach($additional_info as $key => $value) {
				$new_dependency[$key] = $value;
			}
			
			$dependencies[] = $new_dependency;
		}
		
		protected function encode_id($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_id<br />");
			
			$return_object['id'] = $object->slug;
		}
		
		protected function encode_status($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_status<br />");
			
			$return_object['status'] = 'existing';
		}
		
		protected function encode_content($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_content<br />");
			//var_dump($object);
			
			if(!isset($return_object['data'])) $return_object['data'] = array();
			
			$return_object['data']['name'] = $object->name;
			$return_object['data']['slug'] = $object->slug;
			$return_object['data']['taxonomy'] = $object->taxonomy;
			$return_object['data']['description'] = $object->description;
			
			$parent_id = $object->parent;
			if($parent_id) {
				$parent = get_term_by('id', $parent_id, $object->taxonomy);
				
				$term_transfer_id = ost_get_term_transfer_id($parent);
				$return_object['parent'] = $term_transfer_id;
				
				$return_object['dependencies'][] = array('type' => 'term', 'id' => $term_transfer_id);
			}
		}
		
		protected function encode_meta_data($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\TermEncoderBaseObject::encode_meta_data<br />");
			
			$term_id = intval($object->term_id);
			
			if(!isset($return_object['meta_data'])) $return_object['meta_data'] = array();
			if(!isset($return_object['meta_data']['meta'])) $return_object['meta_data']['meta'] = array();
			
			foreach($this->meta_fields as $meta_field) {
				
				$meta_field_name = $meta_field['name'];
				//var_dump($meta_field_name);
				
				$meta_field_data = get_term_meta($term_id, $meta_field_name, true);
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
		
		public function encode_parts($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\TermEncoderBaseObject::encode_parts<br />");
			
			//METODO
			$this->encode_id($object, $return_object);
			$this->encode_content($object, $return_object);
			$this->encode_meta_data($object, $return_object);
		}
		
		public function encode($object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\TermEncoderBaseObject::encode<br />");
			
			$return_data = array();
			$return_data['dependencies'] = array();
			
			$this->encode_parts($object, $return_data);
			
			return $return_data;
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\TermEncoderBaseObject<br />");
		}
	}
?>