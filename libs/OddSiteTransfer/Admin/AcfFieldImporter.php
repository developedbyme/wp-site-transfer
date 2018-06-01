<?php
	namespace OddSiteTransfer\Admin;

	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;

	// \OddSiteTransfer\Admin\AcfFieldImporter
	class AcfFieldImporter {
		
		static function get_field_value($field) {
			
			if($field['type'] === 'repeater') {
				$return_object = array();
			
				foreach($field['value'] as $index => $row) {
					$row_object = array();
					
					foreach($row as $name => $sub_field) {
						$row_object[$name] = self::get_field_value($sub_field);
					}
					
					$return_object[] = $row_object;
				}
				
				return $return_object;
			}
			else if($field['type'] === 'group') {
				$return_object = array();
			
				foreach($field['value'] as $name => $sub_field) {
					$return_object[$name] = self::get_field_value($sub_field);
				}
				
				return $return_object;
			}
			else if($field['type'] === 'flexible_content') {
				$return_object = array();
			
				foreach($field['value'] as $index => $row) {
					$row_object = array();
					
					$row_object['acf_fc_layout'] = $row['layout'];
					
					foreach($row['fields'] as $name => $sub_field) {
						$row_object[$name] = self::get_field_value($sub_field);
					}
					
					$return_object[] = $row_object;
				}
				
				return $return_object;
			}
			
			return $field['value'];
		}
		
		static function update_acf_field($name, $field, $object) {
			//echo("\OddSiteTransfer\Admin\AcfFieldImporter::update_acf_field<br />");
			//var_dump($field);

			if(!isset($field['value'])) return; //METODO: check that this is correct

			switch($field['type']) {
				default:
					echo('Unknown type:'.$field['type']);
				case "textarea":
				case "text":
				case "number":
				case "url":
				case "radio":
				case "wysiwyg":
				case "true_false":
				case "select":
				case "oembed":
				case 'date_time_picker':
					update_field($name, $field['value'], $object);
					break;
				case "date_picker":
					$date_value = \DateTime::createFromFormat('Y-m-d', $field['value']);
					if($date_value) {
						update_field($name, $date_value->format('Ymd'), $object);
					}
					else {
						update_field($name, $field['value'], $object);
					}
					break;
				case "post_object":
				case "image":
				case "relationship":
					$resolved_ids = self::get_dependency_post_ids($field['value']);
					update_field($name, $resolved_ids, $object);
					break;
				case "taxonomy":
					if(isset($field['value']['ids'])) {
						$ids = $field['value']['ids'];
					}
					else {
						$ids = array();
					}
					$taxonomy = $field['value']['taxonomy'];

					if(is_array($ids)) {
						$resolved_ids = self::get_term_dependencies($ids);
					}
					else {
						$resolved_ids = null;
						if(!empty($ids)) {
							$term = ost_get_dependency($ids, 'term');
							if($term) {
								$resolved_ids = intval($term->term_id);
							}
						}
					}
					
					update_field($name, $resolved_ids, $object);
					break;
				case "repeater":
				case "group":
				case "flexible_content":
					update_field($name, self::get_field_value($field), $object);
					break;
			}
		}
		
		static function get_dependency_post_ids($ids) {
			//echo("\OddSiteTransfer\Admin\AcfFieldImporter::get_dependency_post_ids<br />");
			//var_dump($ids);

			if(is_array($ids)) {
				$return_array = array();

				foreach($ids as $id) {
					$current_post = ost_get_dependency($id, 'post');
					if($current_post) {
						$return_array[] = $current_post->ID;
					}
				}

				return $return_array;
			}
			if(empty($ids)) {
				return '';
			}
			$current_post = ost_get_dependency($ids, 'post');
			if($current_post) {
				return $current_post->ID;
			}
			return null;
		}

		static function get_term_dependencies($ids) {
			$return_array = array();
			
			foreach($ids as $id) {
				if($id !== '') {
					$term = ost_get_dependency($id, 'term');
					if($term) {
						$return_array[] = intval($term->term_id);
					}
				}
			}

			return $return_array;
		}

		public static function test_import() {
			echo("Imported \OddSiteTransfer\Admin\AcfFieldImporter<br />");
		}
	}
?>
