<?php
	namespace OddSiteTransfer\Admin;

	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;

	// \OddSiteTransfer\Admin\UserImporter
	class UserImporter {

		function __construct() {
			//echo("\OddSiteTransfer\Admin\UserImporter::__construct<br />");


		}

		protected function get_post_by_transfer_id($post_type, $id) {
			//echo("\OddSiteTransfer\Admin\PostImporter::perform_call<br />");
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
					$return_array[$type.'_'.$id] = ost_get_dependency($id, $type);
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
					$return_array[$type.'_'.$id] = ost_get_dependency($id, $type);
					break;
				default:
					//METODO: error report
					$missing_dependencies[] = $dependency_data;
			}
		}
		
		public function import($transfer_id, $data) {
			echo("\OddSiteTransfer\Admin\UserImporter::import<br />");
			
			//METODO
			
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
			$user_data = $data['data'];
			
			$existing_user = get_user_by('login', $transfer_id);
			
			if($existing_user) {
				$user_data['ID'] = $existing_user->ID;
				$new_id = wp_update_user($user_data);
			}
			else {
				$transfer_email = $user_data['user_email'];
				$existing_user = get_user_by('email', $transfer_email);
			
				if($existing_user) {
					$user_data['ID'] = $existing_user->ID;
					$new_id = wp_update_user($user_data);
				}
				else {
					$new_id = wp_insert_user($user_data);
					if(is_wp_error($new_id)) {
						$error_string = '';
						$errors = $new_id->get_error_messages();
						foreach ($errors as $error) {
							$error_string .= $error;
						}
						return $this->output_error($error_string);
					}
				}
			}
			
			return $this->output_success(array('missingDependencies' => $missing_dependencies));
		}

		public static function test_import() {
			echo("Imported \OddSiteTransfer\Admin\UserImporter<br />");
		}
	}
?>
