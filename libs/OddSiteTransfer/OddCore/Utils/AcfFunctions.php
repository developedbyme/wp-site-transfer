<?php
	namespace OddSiteTransfer\OddCore\Utils;
	
	use \WP_Query;
	
	// \OddSiteTransfer\OddCore\Utils\AcfFunctions
	class AcfFunctions {
		
		public static function ensure_post_has_fields($post) {
			$field_groups = acf_get_field_groups();
			
			if( !empty($field_groups) ) {
			
				foreach( $field_groups as $i => $field_group ) {
					
					$post_id = $post->ID;
					
					$visibility = acf_get_field_group_visibility( $field_group, array(
						'post_id'	=> $post_id, 
						'post_type'	=> $post->post_type
					));
					
					if($visibility) {
						//var_dump($field_group);
						$fields = acf_get_fields( $field_group );
						//var_dump($fields);
						
						foreach($fields as $field) {
							$field_name = $field['name'];
							$field_key = $field['key'];
							
							update_post_meta($post_id, '_'.$field_name, $field_key);
						}
					}
				}
			}
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\OddCore\Utils\AcfFunctions<br />");
		}
	}
?>