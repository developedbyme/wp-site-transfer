<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	use \WP_Post;
	use \OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder
	class AcfPostEncoder extends PostEncoderBaseObject {
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder::__construct<br />");
			
			
		}
		
		protected function encode_acf_field($acf_field, $post_id, $override_value = NULL) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder::encode_acf_field<br />");
			//echo($acf_field['type']."<br />");
			
			$current_send_field = NULL;
			
			switch($acf_field['type']) {
				case "repeater":
					$rows_array = array();
					$current_key = $acf_field['key'];
					if(have_rows($current_key, $post_id)) {
						while(have_rows($current_key, $post_id)) {
							
							the_row();
							$current_row = get_row();
							
							$row_result = array();
							
							foreach($current_row as $key => $value) {
								$current_row_field = get_field_object($key, $post_id, false, true);
								$row_result[$current_row_field['name']] = $this->encode_acf_field($current_row_field, $post_id, $value);
							}
							
							array_push($rows_array, $row_result);
						}
					}
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => $rows_array
					);
					break;
				case "post_object":
					$linked_post_ids = $override_value ? $override_value : $acf_field['value'];
					//var_dump($linked_post_ids);
					$linked_post_local_ids = array();
					
					
					if($linked_post_ids) {
						if($linked_post_ids instanceof WP_Post) {
							$local_id = $this->get_post_transfer_id($linked_post_ids);
							//METODO
							//$this->add_dependency($local_id);
							
							$linked_post_local_ids = $local_id;
							//METODO: handle string instead of array on the recivien end
						}
						else {
							foreach($linked_post_ids as $linked_post_id) {
							
								$local_id = $this->get_post_transfer_id(get_post($linked_post_id));
								//METODO
								//$this->add_dependency($local_id);
							
								$linked_post_local_ids[] = $local_id;
							}
						}
					}
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => $linked_post_local_ids
					);
					break;
				break;
				default:
					echo("Unknown type: ".$acf_field['type']."<br />");
					var_dump($acf_field);
				case "radio":
				case "textarea":
				case "url":
				case "text":
				case "number":
				case "wysiwyg":
				case "true_false":
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => $acf_field['value']
					);
					if($override_value) {
						$current_send_field['value'] = $override_value;
					}
					break;
			}
			
			//echo("// \OddSiteTransfer\Admin\TransferHooks::encode_acf_field<br />");
			return $current_send_field;
		}
		
		protected function encode_meta_data($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder::encode_meta_data<br />");
			
			parent::encode_meta_data($object, $return_object);
			
			//METODO
			$send_fields = array();
			
			$post_id = $object->ID;
			setup_postdata($object); 
			$acf_fields = get_field_objects($post_id);
			
			foreach($acf_fields as $name => $acf_field) {
				$send_fields[$name] = $this->encode_acf_field($acf_field, $post_id);
			}
			wp_reset_postdata();
			
			$return_object['meta_data']['acf'] = $send_fields;
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder<br />");
		}
	}
?>