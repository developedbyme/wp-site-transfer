<?php
	namespace OddSiteTransfer\RestApi\TransferWithDependency;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\TransferWithDependency\SyncTermEndPoint
	class SyncTermEndPoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\SyncTermEndPoint::__construct<br />");
			
			
		}
		
		protected function get_post_by_transfer_id($post_type, $id) {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\SyncPostEndPoint::perform_call<br />");
			//var_dump($id);
			
			$args = array(
				'post_type' => $post_type,
				'post_status' => 'any',
				'meta_key'     => '_odd_server_transfer_id',
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
					$post = $this->get_post_by_transfer_id($dependency_data['post_type'], $id);
					if($post) {
						$return_array[$type.'_'.$id] = $post;
					}
					else {
						$missing_dependencies[] = $dependency_data;
					}
					break;
				case "term":
					$taxonomy = $dependency_data['taxonomy'];
					$term = get_term_by('slug', $id, $dependency_data['taxonomy']);
					if($term) {
						$return_array[$type.'_'.$taxonomy.'_'.$id] = $term;
					}
					else {
						$missing_dependencies[] = $dependency_data;
					}
					break;
				case "user":
					$user = get_user_by('login', $id);
					if($user) {
						$return_array[$type.'_'.$id] = $user;
					}
					else {
						$missing_dependencies[] = $dependency_data;
					}
					break;
				default:
					//METODO: error report
					$missing_dependencies[] = $dependency_data;
			}
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\SyncTermEndPoint::perform_call<br />");
			
			$dependencies = $data['dependencies'];
			
			$resolved_dependencies = array();
			$missing_dependencies = array();
			
			if($dependencies) {
				foreach($dependencies as $dependency) {
					//var_dump($dependency);
				
					$this->get_dependency($dependency, $resolved_dependencies, $missing_dependencies);
				}
			}
			
			$transfer_id = $data['id'];
			$term_data = $data['data'];
			$taxonomy = $term_data['taxonomy'];
			
			$existing_term = get_term_by('slug', $transfer_id, $taxonomy);
			
			if(isset($data['parent'])) {
				$parent_term = get_term_by('slug', $data['parent'], $taxonomy);
				if($parent_term) {
					$term_data['parent'] = intval($parent_term->term_id);
				}
			}
			
			if($existing_term) {
				$result = wp_update_term(intval($existing_term->term_id), $taxonomy, $term_data);
			}
			else {
				$result = wp_insert_term($term_data['name'], $taxonomy, $term_data);
			}
			
			return $this->output_success(array('missingDependencies' => $missing_dependencies));
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\TransferWithDependency\SyncTermEndPoint<br />");
		}
	}
?>