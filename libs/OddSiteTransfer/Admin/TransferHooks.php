<?php
	namespace OddSiteTransfer\Admin;
	
	use \WP_Query;
	use \OddSiteTransfer\OddCore\Utils\HttpLoading as HttpLoading;
	use \OddSiteTransfer\SiteTransfer\Encoders\EncoderSetup as EncoderSetup;
	
	// \OddSiteTransfer\Admin\TransferHooks
	class TransferHooks {
		
		protected $settings = null;
		
		function __construct() {
			//echo("\OddSiteTransfer\Admin\TransferHooks::__construct<br />");
			
			
		}
		
		public function register() {
			//echo("\OddSiteTransfer\Admin\TransferHooks::register<br />");
			
			add_action('save_post', array($this, 'hook_save_post'), 100, 3);
			//METODO: delete post
			add_action('created_term', array($this, 'hook_created_term'), 100, 3);
			add_action('edited_term', array($this, 'hook_edited_term'), 100, 3);
			add_action('pre_delete_term', array($this, 'hook_delete_term'), 100, 2);
			
			add_action('admin_notices', array($this, 'hook_admin_notices'));
		}
		
		protected function check_dependencies($dependencies) {
			
			$ids_to_update = array();
			
			foreach($dependencies as $dependency) {
				
				$transfer_post_id = ost_get_dependency_for_transfer($dependency['id'], $dependency['type']);
				if($transfer_post_id !== -1) {
					$current_hash = get_post_meta($transfer_post_id, 'ost_encoded_data_hash', true);
					if($current_hash !== $dependency['hash']) {
						$ids_to_update[] = $transfer_post_id;
					}
				}
				else {
					trigger_error('Dependency for '.$dependency['id'].' can\'t be created.', E_USER_WARNING);
				}
			}
			
			return $ids_to_update;
		}
		
		protected function compare_image($file_path, $file_size, $channel_id) {
			
			$base_url = get_post_meta($channel_id, 'url', true);
			
			$url = $base_url.'compare/image';
			
			$send_data = array('path' => $file_path, 'size' => $file_size);
			
			$repsonse_data = HttpLoading::send_request($url, $send_data);
			$this->http_log[] = $repsonse_data;
			$result_object = json_decode($repsonse_data['data'], true);
			
			return $result_object['data']['match'];
		}
		
		protected function transfer_media($media, $channel_id) {
			
			$base_url = get_post_meta($channel_id, 'url', true);
			
			$url = $base_url.'incoming-transfer/image';
			
			$file_path = get_post_meta($media->ID, '_wp_attached_file', true);
			
			$file_to_load = wp_upload_dir()['basedir'].'/'.$file_path;
			
			if(!file_exists($file_to_load)) {
				return false;
			}
			
			$image_exists = $this->compare_image($file_path, filesize($file_to_load), $channel_id);
			
			$image_is_ok = true;
			
			if(!$image_exists) {
				
				$send_data = array('path' => $file_path, 'file' => new \CURLFile($file_to_load, 'image/jpeg'));
			
				$repsonse_data = HttpLoading::send_request_with_file($url, $send_data);
				
				if($repsonse_data && $repsonse_data['code'] === 200) {
					$result_object = json_decode($repsonse_data['data'], true);
				}
				else {
					$image_is_ok = false;
				}
			}
			
			return $image_is_ok;
		}
		
		protected function create_transfer_data($transfer_post_id, $channel_id) {
			
			$post = get_post($transfer_post_id);
			
			$transfer_id = get_post_meta($transfer_post_id, 'ost_id', true);
			$type = get_post_meta($transfer_post_id, 'ost_transfer_type', true);
			$data = get_post_meta($transfer_post_id, 'ost_encoded_data', true);
			
			
			$return_data = array(
				'id' => $transfer_id,
				'type' => $type,
				'name' => $post->post_title,
				'data' => $data,
				'hash' => get_post_meta($transfer_post_id, 'ost_encoded_data_hash', true)
			);
			
			if($type === 'media') {
				$media = get_post(ost_get_post_id_for_transfer($transfer_id));
				$media_is_ok = $this->transfer_media($media, $channel_id);
				if(!$media_is_ok) {
					trigger_error('Media '.$post->post_title.' didn\'t transfer.', E_USER_ERROR);
				}
			}
			
			return $return_data;
		}
		
		public function send_outgoing_transfer($transfer_post_id) {
			
			$return_data = array();
			
			$args = array(
				'post_type' => 'ost_channel',
				'fields' => 'ids',
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key' => 'settings_name',
						'value' => 'outgoing',
						'compare' => '='
					)
				)
			);
			
			$channel_ids = get_posts($args);
			
			foreach($channel_ids as $channel_id) {
				$current_log = $this->send_outgoing_transfer_to_channel($transfer_post_id, $channel_id);
				$return_data[] = array('id' => $channel_id, 'name' => get_the_title($channel_id), 'log' => $current_log);
			}
			
			//update_post_meta($transfer_post_id, 'ost_transfer_status', 1); //MEDEBUG: //
			
			return $return_data;
		}
		
		protected function send_outgoing_transfer_to_channel($transfer_post_id, $channel_id) {
			
			$return_log = array();
			
			if($transfer_post_id) {
				
				$base_url = get_post_meta($channel_id, 'url', true);
				
				$transfer_url = $base_url.'incoming-transfer';
				$import_url = $base_url.'run-imports';
				
				$items = array(
					$transfer_post_id
				);
				
				$import_ids = array();
				
				$debug_counter = 0;
				$current_index = 0;
				$number_of_items_per_transfer = 10;
				
				while($current_index < count($items)) {
					if($debug_counter++ > 10) {
						trigger_error('Loop has reached maximum number of times.', E_USER_ERROR);
						break;
					}
					
					$body_items = array();
					
					$items_to_add = min($number_of_items_per_transfer, count($items)-$current_index);
					for($i = 0; $i < $items_to_add; $i++) {
						$current_transfer_post_id = $items[$current_index];
						$body_items[] = $this->create_transfer_data($current_transfer_post_id, $channel_id);
						
						$current_transfer_id = get_post_meta($current_transfer_post_id, 'ost_id', true);
						array_unshift($import_ids, $current_transfer_id);
						
						$current_index++;
					}
					
					$body = array(
						'items' => $body_items
					);
				
					$transfer_response = HttpLoading::send_json_request($transfer_url, $body);
					$return_log[] = $transfer_response;
					
					$encoded_transfer_response = json_decode($transfer_response['data'], true);
					$dependencies_to_update = $this->check_dependencies($encoded_transfer_response['data']['dependencies']);
					
					foreach($dependencies_to_update as $dependency_to_update) {
						if(!in_array($dependency_to_update, $items)) {
							array_push($items, $dependency_to_update);
						}
					}
				}
				
				$debug_counter = 0;
				$current_index = 0;
				$number_of_items_per_import = 5;
				
				while($current_index < count($import_ids)) {
					if($debug_counter++ > 10) {
						trigger_error('Loop has reached maximum number of times.', E_USER_ERROR);
						break;
					}
					
					$body_ids = array();
					
					$items_to_add = min($number_of_items_per_transfer, count($import_ids)-$current_index);
					for($i = 0; $i < $items_to_add; $i++) {
						$body_ids[] = $import_ids[$current_index];
						$current_index++;
					}
					
					$body = array(
						'ids' => $body_ids
					);
				
					$import_response = HttpLoading::send_json_request($import_url, $body);
					$return_log[] = $import_response;
				}
			}
			
			return $return_log;
		}
		
		public function hook_save_post($post_id, $post, $update) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_save_post<br />");
			
			if(wp_is_post_revision($post_id) || $post->post_status === 'auto-draft') {
				return;
			}
			
			$transfer_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/post_transfer_type', null, $post->ID, $post);
			
			if($transfer_type !== null) {
				$transfer_id = ost_get_post_transfer_id($post);
				$transfer_post_id = ost_get_transfer_post_id($transfer_id);
				
				if($transfer_post_id === -1) {
					$transfer_update_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/post_transfer_update_type', null, $post->ID, $post);
					if($transfer_update_type === 'always') {
						$transfer_post_id = ost_add_post_transfer($transfer_id, $transfer_type, $post);
					}
				}
				
				if($transfer_post_id !== -1) {
					ost_update_post_transfer($transfer_post_id, $post);
				}
			}
		}
		
		public function hook_created_term($term_id, $tt_id, $taxonomy) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_created_term<br />");
			
			$term = get_term_by('id', $term_id, $taxonomy);
			
			$transfer_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/term_transfer_type', null, $term_id, $term);
			
			if($transfer_type !== null) {
				$transfer_id = ost_get_term_transfer_id($term);
				$transfer_post_id = ost_get_transfer_post_id($transfer_id);
				
				if($transfer_post_id === -1) {
					$transfer_update_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/term_transfer_update_type', null, $term_id, $term);
					if($transfer_update_type === 'always') {
						$transfer_post_id = ost_add_term_transfer($transfer_id, $transfer_type, $term);
					}
				}
				
				if($transfer_post_id !== -1) {
					ost_update_term_transfer($transfer_post_id, $term);
				}
			}
		}
		
		public function hook_edited_term($term_id, $tt_id, $taxonomy) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_edited_term<br />");
			
			$term = get_term_by('id', $term_id, $taxonomy);
			
			$transfer_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/term_transfer_type', null, $term_id, $term);
			
			if($transfer_type !== null) {
				$transfer_id = ost_get_term_transfer_id($term);
				$transfer_post_id = ost_get_transfer_post_id($transfer_id);
				
				if($transfer_post_id === -1) {
					$transfer_update_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/term_transfer_update_type', null, $term_id, $term);
					if($transfer_update_type === 'always') {
						$transfer_post_id = ost_add_term_transfer($transfer_id, $transfer_type, $term);
					}
				}
				
				if($transfer_post_id !== -1) {
					ost_update_term_transfer($transfer_post_id, $term);
				}
			}
		}
		
		public function hook_delete_term($term_id, $taxonomy) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_delete_term<br />");
			
			$term = get_term_by('id', $term_id, $taxonomy);
			
			$transfer_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/term_transfer_type', null, $term_id, $term);
			
			if($transfer_type !== null) {
				$transfer_id = ost_get_term_transfer_id($term);
				$transfer_post_id = ost_get_transfer_post_id($transfer_id);
				
				if($transfer_post_id !== -1) {
					ost_update_term_transfer_for_deleted($transfer_post_id, $term);
				}
			}
		}
		
		protected function output_notice($module_name, $data, $type = '') {
			$element_id = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
			?>
				
				<div class="notice <?php echo($type); ?>" id="<?php echo($element_id); ?>">
					<script type="text/javascript">
						window.OA.reactModuleCreator.createModule("<?php echo($module_name); ?>", document.getElementById("<?php echo($element_id); ?>"), <?php echo(json_encode($data)); ?>);
					</script>
				</div>
				
			<?php
		}
		
		
		
		public function hook_admin_notices() {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_admin_notices<br />");
			
			$screen = get_current_screen();
			//var_dump($screen);
			
			if(!$screen) {
				return;
			}
			
			global $post;
			
			if($screen->base === 'post' && $screen->post_type === 'ost_channel') {
				
				if($post->post_status === 'publish' || $post->post_status === 'draft') {
					
					$module_data = array('id' => $post->ID);
					
					$base_url = get_post_meta($post->ID, 'url', true);
					
					if($post->post_status !== 'draft' || $base_url) {
						$url = $base_url.'info';
					
						$result_data = HttpLoading::load($url, array());
					
						$module_data['status'] = 'notConencted';
						$module_data['info'] = null;
						$module_data['httpCode'] = $result_data['code'];
						$module_data['loadedData'] = $result_data['data'];
						$notice_type = 'error';
					
						if($result_data['code'] == '200') {
							$loaded_data = json_decode($result_data['data']);
							if($loaded_data->code === 'success') {
								$module_data['info'] = $loaded_data->data;
								
								if($post->post_status !== 'draft') {
									$module_data['status'] = 'connected';
									$notice_type = 'updated';
								}
								else {
									$module_data['status'] = 'connectionWorks';
									$notice_type = '';
								}
							}
						}
						
						$this->output_notice('syncTestNotice', $module_data, $notice_type);
					}
				}
			}
			else if($screen->base === 'post') {
				/*
				$is_incoming_link = get_post_meta($post->ID, '_odd_server_transfer_is_incoming', true);
				
				if($is_incoming_link) {
					$module_data = array('id' => $post->ID, 'syncDate' => get_post_meta($post->ID, '_odd_server_transfer_incoming_sync_date', true), 'syncId' => get_post_meta($post->ID, '_odd_server_transfer_id', true));
					
					$this->output_notice('incomingSyncNotice', $module_data);
				}
				else {
					$sync_index = intval(get_post_meta($post->ID, '_odd_server_transfer_sync_index', true));
					$sync_index_target = intval(get_post_meta($post->ID, '_odd_server_transfer_sync_index_target', true));
				
					if($sync_index_target > $sync_index) {
					
						$module_data = array('id' => $post->ID, 'transferUrl' => get_home_url().'/wp-json/odd-site-transfer/v2/post/'.($post->ID).'/transfer', 'syncId' => get_post_meta($post->ID, '_odd_server_transfer_id', true));
						$this->output_notice('checkSyncNotice', $module_data);
					}
				}
		*/
			}
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\Admin\TransferHooks<br />");
		}
	}
?>