<?php
	namespace OddSiteTransfer\Admin;

	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	use \OddSiteTransfer\Admin\AcfFieldImporter;

	// \OddSiteTransfer\Admin\PostImporter
	class PostImporter {

		function __construct() {
			//echo("\OddSiteTransfer\Admin\PostImporter::__construct<br />");

		}

		public function filter_wp_kses_allowed_html($allowed_post_tags) {

			if(!isset($allowed_post_tags['span'])) {
				$allowed_post_tags['span'] = array();
			}
			$span_array = $allowed_post_tags['span'];
			$span_array['data-related-ingredients'] = true;
			$allowed_post_tags['span'] = $span_array;

			return $allowed_post_tags;

		}


		protected function get_resolved_post_ids($ids, $resolved_dependencies) {
			//echo("\OddSiteTransfer\Admin\PostImporter::get_resolved_post_ids<br />");
			//var_dump($ids);

			if(is_array($ids)) {
				$return_array = array();

				foreach($ids as $id) {
					$current_post = $this->get_resolved_dependency('post', $id, $resolved_dependencies);
					if($current_post) {
						$return_array[] = $current_post->ID;
					}
				}

				return $return_array;
			}
			if(empty($ids)) {
				return '';
			}
			$current_post = $this->get_resolved_dependency('post', $ids, $resolved_dependencies);
			if($current_post) {
				return $current_post->ID;
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

		protected function get_resolved_term_dependencies($ids, $taxonomy, $resolved_dependencies) {
			$return_array = array();
			
			foreach($ids as $id) {
				if($id !== '') {
					$term = $this->get_resolved_dependency('term', $id, $resolved_dependencies);
					if($term) {
						$return_array[] = intval($term->term_id);
					}
				}
			}

			return $return_array;
		}

		public function import($transfer_id, $data) {
			//echo("\OddSiteTransfer\Admin\PostImporter::import<br />");

			add_filter('wp_kses_allowed_html', array($this, 'filter_wp_kses_allowed_html'), 10, 1);
			
			$transfer_post_id = ost_get_transfer_post_id($transfer_id);
			$transfer_type = get_post_meta($transfer_post_id, 'ost_transfer_type', true);

			$dependencies = $data['dependencies'];

			$resolved_dependencies = array();
			$missing_dependencies = array();

			if($dependencies) {
				foreach($dependencies as $dependency) {
					//var_dump($dependency);

					$this->get_dependency($dependency, $resolved_dependencies, $missing_dependencies);
				}
			}
			
			$post_data = $data['data'];
			$meta_data = $data['meta_data'];
			$taxonomies = $data['taxonomies'];
			
			$existing_post = null;
			$existing_post_id = ost_get_post_id_for_transfer($transfer_id);
			if($existing_post_id > 0) {
				$existing_post = get_post($existing_post_id);
			}

			$author_id = $data['author'];
			
			$author = $this->get_resolved_dependency('user', $author_id, $resolved_dependencies);
			if($author) {
				$post_data['post_author'] = $author->ID;
			}
			
			$parent_id = $data['parent'];
			
			$post_data['post_parent'] = 0;
			
			if($parent_id !== null) {
				$parent = $this->get_resolved_dependency('post', $parent_id, $resolved_dependencies);
				if($parent) {
					$post_data['post_parent'] = $parent->ID;
				}
			}
			

			$new_id = NULL;
			
			$post_data = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/import_post/'.$transfer_type.'/post_data', $post_data, $data, $transfer_id);

			if($existing_post) {
				$post_data['ID'] = $existing_post->ID;
				$new_id = wp_update_post($post_data);
			}
			else {
				$new_id = wp_insert_post($post_data);
			}

			if(is_wp_error($new_id)) {
				//METODO: error message
				return null;
			}
			
			do_action(ODD_SITE_TRANSFER_DOMAIN.'/import_post/'.$transfer_type.'/after_post_data', $new_id, $post_data, $data, $transfer_id);

			if($post_data['post_type'] === 'attachment') {

				$base_file = wp_upload_dir()['basedir'].'/'.$meta_data['meta']['_wp_attached_file'];
				$attachment_metadata = $meta_data['meta']['_wp_attachment_metadata'];

				if(isset($attachment_metadata['sizes'])) {
					foreach($meta_data['meta']['_wp_attachment_metadata']['sizes'] as $image_size) {
						//METODO: check if it has size before resizing
						$resize_result = image_make_intermediate_size($base_file, $image_size['width'], $image_size['height'], true);
						//METODO: check that resize worked
					}
				}
			}
			
			if(isset($meta_data['post_thumbnail_id'])) {
				//METODO: test removal of thumbnail

				$image_post = $this->get_resolved_dependency('post', $meta_data['post_thumbnail_id'], $resolved_dependencies);
				
				//METODO: test this
				
				if($image_post) {
					set_post_thumbnail($new_id, $image_post->ID);
				}
			}

			if(isset($taxonomies)) {
				foreach($taxonomies as $taxonomy => $term_ids) {
					$int_term_ids = $this->get_resolved_term_dependencies($term_ids, $taxonomy, $resolved_dependencies);
					wp_set_object_terms($new_id, $int_term_ids, $taxonomy, false);
				}

			}

			if(isset($meta_data['acf'])) {
				\OddSiteTransfer\OddCore\Utils\AcfFunctions::ensure_post_has_fields(get_post($new_id));
				
				foreach($meta_data['acf'] as $name => $field) {
					AcfFieldImporter::update_acf_field($name, $field, $new_id);
				}
			}

			if(isset($meta_data['meta'])) {
				foreach($meta_data['meta'] as $key => $value) {
					update_post_meta($new_id, $key, $value);
				}
			}

			if(isset($meta_data['meta_posts'])) {
				foreach($meta_data['meta_posts'] as $key => $value) {
					update_post_meta($new_id, $key, $this->get_resolved_post_ids($value, $resolved_dependencies));
				}
			}
			
			foreach($meta_data as $category_name => $fields) {
				do_action(ODD_SITE_TRANSFER_DOMAIN.'/import_post/'.$transfer_type.'/meta/'.$category_name, $new_id, $fields, $transfer_id);
			}

			update_post_meta($new_id, 'ost_transfer_id', $transfer_id);
			update_post_meta($new_id, 'ost_import_date', date(DATE_ATOM));

			return array('url' => get_permalink($new_id), 'missingDependencies' => $missing_dependencies);

		}

		public static function test_import() {
			echo("Imported \OddSiteTransfer\Admin\PostImporter<br />");
		}
	}
?>
