<?php
	namespace OddSiteTransfer\Admin;
	
	use \WP_Query;
	use \OddSiteTransfer\Admin\AcfFieldImporter;
	
	// \OddSiteTransfer\Admin\TermImporter
	class TermImporter {
		
		function __construct() {
			//echo("\OddSiteTransfer\Admin\TermImporter::__construct<br />");
			
			
		}
		
		public function import($transfer_id, $data) {
			//echo("\OddSiteTransfer\Admin\TermImporter::perform_call<br />");
			
			$term_data = $data['data'];
			
			$taxonomy = $term_data['taxonomy'];
			
			$transfer_post_id = ost_get_transfer_post_id($transfer_id);
			$transfer_type = get_post_meta($transfer_post_id, 'ost_transfer_type', true);
			
			$existing_term = ost_get_term_for_transfer($transfer_id);
			
			if($data['status'] === 'non-existing') {
				if($existing_term) {
					wp_delete_term( $existing_term->term_id, $taxonomy );
				}
				
				return null;
			}
			
			if(isset($data['parent'])) {
				$parent_term = ost_get_dependency($data['parent'], 'term');
				if($parent_term) {
					$term_data['parent'] = intval($parent_term->term_id);
				}
			}
			
			if(!$existing_term) {
				$result = wp_insert_term($term_data['name'], $taxonomy, $term_data);
				if(is_wp_error($result)) {
					$existing_term = get_term_by('id', $result->error_data['term_exists'], $taxonomy);
				}
				else {
					$existing_term = get_term_by('id', $result['term_id'], $taxonomy);
				}
				
				update_metadata('term', $existing_term->term_id, 'ost_transfer_id', $transfer_id);
			}
			
			wp_update_term(intval($existing_term->term_id), $existing_term->taxonomy, $term_data);
			
			//METODO: set meta data
			$meta_data = $data['meta_data'];
			
			if(isset($meta_data['acf'])) {
				//\OddSiteTransfer\OddCore\Utils\AcfFunctions::ensure_post_has_fields(get_post($new_id));
				
				foreach($meta_data['acf'] as $name => $field) {
					AcfFieldImporter::update_acf_field($name, $field, $existing_term);
				}
			}
			
			foreach($meta_data as $category_name => $fields) {
				do_action(ODD_SITE_TRANSFER_DOMAIN.'/import_term/'.$transfer_type.'/meta/'.$category_name, intval($existing_term->term_id), $fields, $transfer_id);
			}
			
			do_action(ODD_SITE_TRANSFER_DOMAIN.'/import_term/'.$transfer_type.'/after_import', intval($existing_term->term_id), $term_data, $data, $transfer_id);
			
			return intval($existing_term->term_id);
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\Admin\TermImporter<br />");
		}
	}
?>