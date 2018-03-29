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
			
			add_action('save_post', array($this, 'hook_save_post'), 10, 3);
			//METODO: delete post
			add_action('created_term', array($this, 'hook_created_term'), 10, 3);
			add_action('edited_term', array($this, 'hook_edited_term'), 10, 3);
			add_action('delete_term', array($this, 'hook_delete_term'), 10, 3);
			
			//add_action('admin_notices', array($this, 'hook_admin_notices'));
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
		
		protected function create_transfer_data($transfer_post_id) {
			$post = get_post($transfer_post_id);
			
			$data = get_post_meta($transfer_post_id, 'ost_encoded_data', true);
			
			$return_data = array(
				'id' => $transfer_id,
				'type' => get_post_meta($transfer_post_id, 'ost_transfer_type', true),
				'name' => $post->post_title,
				'data' => $data,
				'hash' => get_post_meta($transfer_post_id, 'ost_encoded_data_hash', true)
			);
			
			return $return_data;
		}
		
		public function send_outgoing_transfer($transfer_post_id) {
			
			$transfer_id = get_post_meta($transfer_post_id, 'ost_id', true);
			
			if($transfer_post_id) {
				//METODO: channels
				$url = 'http://transfer2.localhost/wp-json/ost/v3/incoming-transfer';
				
				$body = array(
					'items' => array(
						$this->create_transfer_data($transfer_post_id)
					)
				);
				
				$transfer_response = HttpLoading::send_json_request($url, $body);
				$encoded_transfer_response = json_decode($transfer_response['data'], true);
				$dependencies_to_update = $this->check_dependencies($encoded_transfer_response['data']['dependencies']);
				var_dump($dependencies_to_update);
				
				$url = 'http://transfer2.localhost/wp-json/ost/v3/run-imports';
				
				$body = array(
					'ids' => array(
						$transfer_id
					)
				);
				
				HttpLoading::send_json_request($url, $body);
			}
		}
		
		public function hook_save_post($post_id, $post, $update) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_save_post<br />");
			
			if(wp_is_post_revision($post_id)) {
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
			
			//METODO
		}
		
		public function hook_edited_term($term_id, $tt_id, $taxonomy) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_edited_term<br />");
			
			//METODO
		}
		
		public function hook_delete_term($term_id, $tt_id, $taxonomy) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_delete_term<br />");
			
			//METODO
		}
		
		/*
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
			
			if($screen->base === 'post' && $screen->post_type === 'server-transfer') {
				
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
			}
		}
		*/
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\Admin\TransferHooks<br />");
		}
	}
?>