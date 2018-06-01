<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	use \WP_Post;
	use \OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\AcfFieldEncoder
	class AcfFieldEncoder {
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\AcfFieldEncoder::__construct<br />");
		}
		
		static function add_dependency($type, $id, &$dependencies) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\AcfFieldEncoder::add_dependency<br />");
			
			$new_dependency = array('type' => $type, 'id' => $id);
			
			$dependencies[] = $new_dependency;
		}
		
		static function get_referenced_posts($post_ids, &$dependencies) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\AcfFieldEncoder::get_referenced_posts<br />");
			
			if(is_array($post_ids)) {
				$return_array = array();
				foreach($post_ids as $post_id) {
					if($post_id instanceof WP_Post) {
						$post_id = $post_id->ID;
					}
					$linked_post = get_post($post_id);
					
					if($linked_post) {
						$linked_post_id = ost_get_post_transfer_id($linked_post);
						self::add_dependency('post', $linked_post_id, $dependencies);
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
					$linked_post_id = ost_get_post_transfer_id($linked_post);
					self::add_dependency('post', $linked_post_id, $dependencies);
				
					return $linked_post_id;
				}
			}
			
			return null;
		}
		
		static function get_referenced_terms($term_ids, &$dependencies) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\AcfFieldEncoder::get_referenced_terms<br />");
			
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
						
						self::add_dependency('term', $term_transfer_id, $dependencies);
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
					self::add_dependency('term', $term_transfer_id, $dependencies);
				
					return $term_transfer_id;
				}
			}
			
			return null;
		}
		
		static function encode_acf_field($field_value, $acf_field, $owner_object, &$dependencies) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\AcfFieldEncoder::encode_acf_field<br />");
			//echo($acf_field['type']."<br />");
			
			$current_send_field = NULL;
			
			switch($acf_field['type']) {
				case "flexible_content":
					$rows_array = array();
				
					$repeater_value = $field_value;
					
					$current_send_field = array(
						'type' => $acf_field['type'],
					);
					
					if(is_array($repeater_value)) {
						foreach($repeater_value as $index => $current_row) {
						
							$row_layout = null;
							$row_result = array();
							foreach($current_row as $key => $value) {
							
								if($key === 'acf_fc_layout') {
									$row_layout = $value;
									continue;
								}
							
								$current_row_field = get_field_object($key, $owner_object, false, true);
								$row_result[$current_row_field['name']] = self::encode_acf_field($value, $current_row_field, $owner_object, $dependencies);
							}
					
							$rows_array[] = array('layout' => $row_layout, 'fields' => $row_result);
						}
					}
				
					$current_send_field['value'] = $rows_array;
				
					break;
				case "repeater":
					
					$rows_array = array();
					
					$repeater_value = $field_value;
					
					if(is_array($repeater_value)) {
						foreach($repeater_value as $index => $current_row) {
						
							$row_result = array();
							foreach($current_row as $key => $value) {
								$current_row_field = get_field_object($key, $owner_object, false, true);
								$row_result[$current_row_field['name']] = self::encode_acf_field($value, $current_row_field, $owner_object, $dependencies);
							}
						
							$rows_array[] = $row_result;
						}
					}
					
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => $rows_array
					);
					
					
					break;
				case "group":
					$value_object = array();
					
					if(is_array($field_value)) {
						foreach($field_value as $key => $value) {
						
							$current_row_field = get_field_object($key, $owner_object, false, true);
							$encoded_value = self::encode_acf_field($value, $current_row_field, $owner_object, $dependencies);
						
							$value_object[$current_row_field['name']] =  $encoded_value;
						}
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
					$linked_post_ids = $field_value;
					//var_dump($linked_post_ids);
					if(isset($linked_post_ids)) {
						$linked_post_local_ids = self::get_referenced_posts($linked_post_ids, $dependencies);
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
					$linked_taxonomy_ids = $field_value;
					if(isset($linked_taxonomy_ids)) {
						$linked_taxonomy_ids = self::get_referenced_terms($linked_taxonomy_ids, $dependencies);
					}
					else {
						$linked_taxonomy_ids = array();
					}
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => array('ids' => $linked_taxonomy_ids, 'taxonomy' => $acf_field['taxonomy'])
					);
					break;
				case "date_picker":
					$current_send_field = array(
						'type' => $acf_field['type']
					);
					$date_value = \DateTime::createFromFormat('Ymd', $field_value);
					if($date_value) {
						$current_send_field['value'] = $date_value->format('Y-m-d');
					}
					else {
						$current_send_field['value'] = $field_value;
					}
					break;
				case "number":
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => (float)$field_value
					);
					break;
				case 'bool':
				case 'true_false':
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => ($field_value == 1)
					);
					break;
				default:
					echo("Unknown type: ".$acf_field['type']."<br />");
					var_dump($acf_field);
				case "oembed":
				case "radio":
				case "textarea":
				case "url":
				case "text":
				case "wysiwyg":
				case "true_false":
				case "select":
				case "oembed":
				case 'button_group':
				case 'date_time_picker':
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => $field_value
					);
					break;
			}
			
			//echo("// \OddSiteTransfer\Admin\TransferHooks::encode_acf_field<br />");
			return $current_send_field;
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\AcfFieldEncoder<br />");
		}
	}
?>