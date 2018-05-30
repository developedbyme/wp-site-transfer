<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	use \WP_Post;
	use \OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder
	class AcfPostEncoder extends PostEncoderBaseObject {
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder::__construct<br />");
			
			parent::__construct();
		}
		
		protected function encode_acf_field($acf_field, $post_id, &$dependencies, $override_value = null) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder::encode_acf_field<br />");
			//echo($acf_field['type']."<br />");
			
			$current_send_field = NULL;
			
			//METODO: replace all override switches inside of the switch
			$field_value = $acf_field['value'];
			if($override_value) {
				$field_value = $override_value;
			}
			
			switch($acf_field['type']) {
				case "flexible_content":
					$rows_array = array();
				
					$repeater_value = $acf_field['value'];
					if($override_value) {
						$repeater_value = $override_value;
					}
					
					$current_send_field = array(
						'type' => $acf_field['type'],
					);
				
					foreach($repeater_value as $index => $current_row) {
						
						$row_layout = null;
						$row_result = array();
						foreach($current_row as $key => $value) {
							
							if($key === 'acf_fc_layout') {
								$row_layout = $value;
								continue;
							}
							
							$current_row_field = get_field_object($key, $post_id, false, true);
							$row_result[$current_row_field['name']] = $this->encode_acf_field($current_row_field, $post_id, $dependencies, $value);
						}
					
						$rows_array[] = array('layout' => $row_layout, 'fields' => $row_result);
					}
				
					$current_send_field['value'] = $rows_array;
				
					break;
				case "repeater":
					
					$rows_array = array();
					
					$repeater_value = $acf_field['value'];
					if($override_value) {
						$repeater_value = $override_value;
					}
					
					foreach($repeater_value as $index => $current_row) {
						
						$row_result = array();
						foreach($current_row as $key => $value) {
							$current_row_field = get_field_object($key, $post_id, false, true);
							$row_result[$current_row_field['name']] = $this->encode_acf_field($current_row_field, $post_id, $dependencies, $value);
						}
						
						$rows_array[] = $row_result;
					}
					
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => $rows_array
					);
					
					break;
				case "group":
					$value_object = array();
					
					foreach($acf_field['value'] as $key => $value) {
						
						$current_row_field = get_field_object($key, $post_id, false, true);
						$encoded_value = $this->encode_acf_field($current_row_field, $post_id, $dependencies, $value);
						
						$value_object[$current_row_field['name']] =  $encoded_value;
					}
					
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => $value_object
					);
					
					break;
				case "image":
				case "post_object":
				case "relationship":
				case "gallery":
					$linked_post_ids = $override_value ? $override_value : $acf_field['value'];
					//var_dump($linked_post_ids);
					if(isset($linked_post_ids)) {
						$linked_post_local_ids = $this->get_referenced_posts($linked_post_ids, $dependencies);
					}
					else {
						$linked_post_local_ids = array();
					}
					
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => $linked_post_local_ids
					);
					break;
				break;
				case "taxonomy":
					$linked_taxonomy_ids = $override_value ? $override_value : $acf_field['value'];
					if(isset($linked_taxonomy_ids)) {
						$linked_taxonomy_ids = $this->get_referenced_terms($linked_taxonomy_ids, $dependencies);
					}
					else {
						$linked_taxonomy_ids = array();
					}
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => array('ids' => $linked_taxonomy_ids, 'taxonomy' => $acf_field['taxonomy'])
					);
					break;
				case "oembed":
				case "date_picker":
					$current_send_field = array(
						'type' => $acf_field['type']
					);
					if($override_value) {
						$current_send_field['value'] = $override_value;
					}
					else {
						$raw_field = get_field_object($acf_field['key'], $post_id, false, true);
						$current_send_field['value'] = $raw_field['value'];
					}
					break;
				case "number":
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => (float)$acf_field['value']
					);
					if($override_value) {
						$current_send_field['value'] = (float)$override_value;
					}
					break;
				case 'bool':
				case 'true_false':
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => ($acf_field['value'] == 1)
					);
					if($override_value) {
						$current_send_field['value'] = ($acf_field['value'] == 1);
					}
					break;
				default:
					echo("Unknown type: ".$acf_field['type']."<br />");
					var_dump($acf_field);
				case "radio":
				case "textarea":
				case "url":
				case "text":
				case "wysiwyg":
				case "true_false":
				case "select":
				case "oembed":
				case 'button_group':
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
			
			//METODO: move this as it's not efficient to have it here
			\OddSiteTransfer\OddCore\Utils\AcfFunctions::ensure_post_has_fields($object);
			
			$send_fields = array();
			
			$post_id = $object->ID;
			setup_postdata($object); 
			$acf_fields = get_field_objects($post_id, false, true);
			
			if($acf_fields) {
				foreach($acf_fields as $name => $acf_field) {
					$send_fields[$name] = $this->encode_acf_field($acf_field, $post_id, $return_object['dependencies']);
				}
			}
			wp_reset_postdata();
			
			$return_object['meta_data']['acf'] = $send_fields;
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder<br />");
		}
	}
?>