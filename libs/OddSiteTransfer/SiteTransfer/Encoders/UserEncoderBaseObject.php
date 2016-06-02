<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	use \WP_User;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject
	class UserEncoderBaseObject {
		
		protected $qualifier = null;
		
		protected $meta_fields = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject::__construct<br />");
			
			
		}
		
		public function set_qualifier($qualifier) {
			$this->qualifier = $qualifier;
			
			return $this;
		}
		
		public function add_meta_field($name, $type = 'data') {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject::add_meta_field<br />");
			$this->meta_fields[] = array('name' => $name, 'type' => $type);
			
			return $this;
		}
		
		public function qualify($object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject::qualify<br />");
			
			if($object instanceof WP_User) {
				return $this->qualifier->qualify($object);
			}
			return false;
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
			
			$return_object['id'] = $object->user_login;
			
		}
		
		protected function encode_status($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject::encode_status<br />");
			
			$return_object['status'] = 'existing';
		}
		
		protected function encode_content($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::encode_content<br />");
			//var_dump($object);
			
			if(!isset($return_object['data'])) $return_object['data'] = array();
			
			$return_object['data']['user_login'] = $user->user_login;
			$return_object['data']['user_nicename'] = $user->user_nicename;
			$return_object['data']['user_email'] = $user->user_email;
			$return_object['data']['display_name'] = $user->display_name;
			$return_object['data']['first_name'] = $user->first_name;
			$return_object['data']['last_name'] = $user->last_name;
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
			$this->encode_status($object, $return_object);
			if($return_object['status'] !== 'non-existing') {
				$this->encode_content($object, $return_object);
				$this->encode_meta_data($object, $return_object);
			}
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