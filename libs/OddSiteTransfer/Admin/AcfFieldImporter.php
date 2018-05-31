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
			
			return $field['value'];
		}
		
		static function update_acf_field($name, $field, $object, $resolved_dependencies) {
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
				case "date_picker":
					update_field($name, $field['value'], $object);
					break;
				case "post_object":
				case "image":
				case "relationship":
					$resolved_ids = self::get_dependency_post_ids($field['value'], $resolved_dependencies);
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
					update_field($name, get_field_value($field), $object);
					break;
				case "flexible_content":
				case "group":
					echo('Implement:'.$field['type']);
				/*
					$new_rows = $field['value'];
					$layouts = array();

					$acf_key = acf_get_field($name)["key"];

					foreach($new_rows as $index => $row_and_layout) {
						//MENOTE: length is set directly to meta data
						
						$row = $row_and_layout['fields'];
						$layouts[] = $row_and_layout['layout'];

						foreach($row as $row_field_name => $row_field) {
							$new_path = array($name, $index+1, $row_field_name);
							$new_meta_path = array($name, $index, $row_field_name);
							if($repeater_path) {
								$new_path = array_merge($repeater_path, array($index+1, $row_field_name));
								$new_meta_path = array_merge($meta_path, array($index, $row_field_name));
							}
							//echo(implode(',', $new_path).'<br />');
							$this->update_acf_field($repeater_path, $row_field, $post_id, $resolved_dependencies, $new_path, $new_meta_path);
						}
					}

					$meta_value_name = $name;
					if($meta_path) {
						$meta_value_name = implode('_', $meta_path);
					}

					update_post_meta($post_id, $meta_value_name, $layouts);
					if($acf_key) {
						update_post_meta($post_id, '_'.$meta_value_name, $acf_key);
					}
				*/
					
					break;
			}
		}
		
		protected function get_dependency_post_ids($ids) {
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
