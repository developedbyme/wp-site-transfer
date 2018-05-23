<?php
	namespace OddSiteTransfer\Admin;
	
	use \WP_Query;
	
	// \OddSiteTransfer\Admin\TermImporter
	class TermImporter {
		
		function __construct() {
			//echo("\OddSiteTransfer\Admin\TermImporter::__construct<br />");
			
			
		}
		
		protected function get_post_by_transfer_id($post_type, $id) {
			//echo("\OddSiteTransfer\Admin\TermImporter::perform_call<br />");
			//var_dump($id);

			remove_all_actions('pre_get_posts');

			if($post_type === 'any') {
				$post_type = get_post_types(array(), 'names');
			}

			$args = array(
				'post_type' => $post_type,
				'post_status' => array('any', 'trash'),
				'meta_key'     => 'ost_transfer_id',
				'meta_value'   => $id,
				'meta_compare' => '='
			);
			$query = new WP_Query( $args );

			//var_dump($query);
			//var_dump($query->have_posts());

			if($query->have_posts()) {
				//METODO: warn for more than 1 match
				return $query->get_posts()[0];
			}

			return null;
		}
		
		protected function get_dependency($dependency_data, &$return_array, &$missing_dependencies) {
			$id = $dependency_data['id'];
			$type = $dependency_data['type'];

			switch($type) {
				case "post":
				case "term":
				case "user":
					$return_array[$type.'_'.$id] = ost_get_dependency($id, $type);
					break;
				default:
					//METODO: error report
					$missing_dependencies[] = $dependency_data;
			}
		}
		
		protected function get_resolved_dependency($type, $id, $resolved_dependencies) {
			$full_id = $type.'_'.$id;
			if(isset($resolved_dependencies[$full_id])) {
				return $resolved_dependencies[$full_id];
			}
			return null;
		}
		
		public function import($transfer_id, $data) {
			//echo("\OddSiteTransfer\Admin\TermImporter::perform_call<br />");
			
			$dependencies = $data['dependencies'];
			
			$resolved_dependencies = array();
			$missing_dependencies = array();
			
			if($dependencies) {
				foreach($dependencies as $dependency) {
					//var_dump($dependency);
				
					$this->get_dependency($dependency, $resolved_dependencies, $missing_dependencies);
				}
			}
			
			$term_data = $data['data'];
			
			$taxonomy = $term_data['taxonomy'];
			
			$existing_term = ost_get_term_for_transfer($transfer_id);
			
			if(isset($data['parent'])) {
				$parent_term = $this->get_resolved_dependency('term', $data['parent'], $resolved_dependencies);
				if($parent_term) {
					$term_data['parent'] = intval($parent_term->term_id);
				}
			}
			
			var_dump($term_data);
			
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
			
			return array('missingDependencies' => $missing_dependencies);
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\Admin\TermImporter<br />");
		}
	}
?>