<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	use \WP_User;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject
	class UserEncoderBaseObject {
		
		protected $meta_fields = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject::__construct<br />");
			
			
		}
		
		public function add_meta_field($name, $type = 'data') {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject::add_meta_field<br />");
			$this->meta_fields[] = array('name' => $name, 'type' => $type);
			
			return $this;
		}
		
		protected function add_dependency($type, $id, $additional_info, &$dependencies) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject::add_dependency<br />");
			
			$new_dependency = array('type' => $type, 'id' => $id);
			
			foreach($additional_info as $key => $value) {
				$new_dependency[$key] = $value;
			}
			
			$dependencies[] = $new_dependency;
		}
		
		protected function encode_id($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject::encode_id<br />");
			
			$return_object['id'] = ost_get_user_transfer_id($object);
			
		}
		
		protected function encode_content($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_content<br />");
			//var_dump($object);
			
			if(!isset($return_object['data'])) $return_object['data'] = array();
			
			$return_object['data']['user_login'] = $object->user_login;
			$return_object['data']['user_nicename'] = $object->user_nicename;
			$return_object['data']['user_email'] = $object->user_email;
			$return_object['data']['display_name'] = $object->display_name;
			$return_object['data']['first_name'] = $object->first_name;
			$return_object['data']['last_name'] = $object->last_name;
		}
		
		protected function encode_meta_data($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject::encode_meta_data<br />");
			
			$user_id = intval($object->ID);
			
			if(!isset($return_object['meta_data'])) $return_object['meta_data'] = array();
			if(!isset($return_object['meta_data']['meta'])) $return_object['meta_data']['meta'] = array();
			
			foreach($this->meta_fields as $meta_field) {
				
				$meta_field_name = $meta_field['name'];
				//var_dump($meta_field_name);
				
				$meta_field_data = get_user_meta($user_id, $meta_field_name, true);
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
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject::encode_parts<br />");
			
			//METODO
			$this->encode_id($object, $return_object);
			$this->encode_content($object, $return_object);
			$this->encode_meta_data($object, $return_object);
		}
		
		public function encode($object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject::encode<br />");
			
			$return_data = array();
			$return_data['dependencies'] = array();
			
			$this->encode_parts($object, $return_data);
			
			return $return_data;
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject<br />");
		}
	}
?>