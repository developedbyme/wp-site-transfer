<?php
	namespace OddSiteTransfer\Admin;
	
	use \WP_Query;
	use \OddSiteTransfer\OddCore\Utils\HttpLoading as HttpLoading;
	
	// \OddSiteTransfer\Admin\TransferHooks
	class TransferHooks {
		
		protected $settings = null;
		
		function __construct() {
			//echo("\OddSiteTransfer\Admin\TransferHooks::__construct<br />");
			
			
		}
		
		protected function create_server_settings() {
			//echo("\OddSiteTransfer\Admin\TransferHooks::create_server_settings<br />");
			$this->settings = array();
			
			//Defaults
			$default_post_type_qualifier = new \OddSiteTransfer\SiteTransfer\Qualifiers\PostTypeQualifier();
			$default_post_type_qualifier->add_post_types(array('oa_recipe', 'oa_product', 'oa_wine', 'oa_wine_producer', 'attachment'));
			
			$recipe_post_type_qualifier = new \OddSiteTransfer\SiteTransfer\Qualifiers\PostTypeQualifier();
			$recipe_post_type_qualifier->add_post_types(array('oa_recipe'));
			
			$all_quailifier = new \OddSiteTransfer\SiteTransfer\Qualifiers\AllQualifier();
			
			$default_post_encoder = new \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder();
			$default_post_encoder->set_qualifier($default_post_type_qualifier);
			
			$default_term_encoder = new \OddSiteTransfer\SiteTransfer\Encoders\TermEncoderBaseObject();
			$default_term_encoder->set_qualifier($all_quailifier);
			
			$default_user_encoder = new \OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject();
			$default_user_encoder->set_qualifier($all_quailifier);
			
			//Zeta
			$zeta_recipe_post_encoder = new \OddSiteTransfer\SiteTransfer\Encoders\TargetedSitePostEncoder();
			$zeta_recipe_post_encoder->add_term('zeta', 'oa_target_site');
			$zeta_recipe_post_encoder->set_qualifier($recipe_post_type_qualifier);
			
			//Wine and friends
			$wf_recipe_post_encoder = new \OddSiteTransfer\SiteTransfer\Encoders\TargetedSitePostEncoder();
			$wf_recipe_post_encoder->add_term('wine-and-friends', 'oa_target_site');
			$wf_recipe_post_encoder->set_qualifier($recipe_post_type_qualifier);
			
			//oa_recipe
			$default_post_encoder->add_meta_field('_has_step_instructions', 'data');
			$default_post_encoder->add_meta_field('_has_matched_ingredients', 'data');
			
			//attachment
			//METODO: should caption be here?
			$default_post_encoder->add_meta_field('_wp_attached_file', 'data');
			$default_post_encoder->add_meta_field('_wp_attachment_metadata', 'data');
			$default_post_encoder->add_meta_field('_wp_attachment_image_alt', 'data');
			
			//--- Settings
			//Default
			$default_server_settings = new \OddSiteTransfer\SiteTransfer\ServerSettings();
			$default_server_settings->add_encoder($default_post_encoder);
			$default_server_settings->add_encoder($default_term_encoder);
			$default_server_settings->add_encoder($default_user_encoder);
			$this->add_server_settings('default', $default_server_settings);
			
			//Zeta
			$zeta_server_settings = new \OddSiteTransfer\SiteTransfer\ServerSettings();
			$zeta_server_settings->add_encoder($zeta_recipe_post_encoder);
			$zeta_server_settings->add_encoder($default_post_encoder);
			$zeta_server_settings->add_encoder($default_term_encoder);
			$zeta_server_settings->add_encoder($default_user_encoder);
			$this->add_server_settings('zeta', $zeta_server_settings);
			
			//Wine & friends
			$zeta_server_settings = new \OddSiteTransfer\SiteTransfer\ServerSettings();
			$zeta_server_settings->add_encoder($wf_recipe_post_encoder);
			$zeta_server_settings->add_encoder($default_post_encoder);
			$zeta_server_settings->add_encoder($default_term_encoder);
			$zeta_server_settings->add_encoder($default_user_encoder);
			$this->add_server_settings('wf', $zeta_server_settings);
		}
		
		public function get_settings() {
			//echo("\OddSiteTransfer\Admin\TransferHooks::get_settings<br />");
			if($this->settings === null) {
				$this->create_server_settings();
			}
			
			return $this->settings;
		}
		
		public function add_server_settings($name, $setting) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::add_server_settings<br />");
			$this->settings[$name] = $setting;
		}
		
		public function register() {
			//echo("\OddSiteTransfer\Admin\TransferHooks::register<br />");
			
			add_action('save_post', array($this, 'hook_save_post'), 10, 3);
			//METODO: delete post
			add_action('created_term', array($this, 'hook_created_term'), 10, 3);
			add_action('edited_term', array($this, 'hook_edited_term'), 10, 3);
			add_action('delete_term', array($this, 'hook_delete_term'), 10, 3);
			
			add_action('admin_notices', array($this, 'hook_admin_notices'));
		}
		
		protected function get_server_transfers() {
			$args = array(
				'post_type' => 'server-transfer',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'ignore_sticky_posts'=> 1
			);
			
			return new WP_Query($args);
		}
		
		public function hook_save_post($post_id, $post, $update) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_save_post<br />");
			
			if(wp_is_post_revision($post_id)) {
				return;
			}
			
			remove_action('save_post', array($this, 'hook_save_post'));
			
			$should_transfer = false;
			
			$settings = $this->get_settings();
			
			$server_transfers_query = $this->get_server_transfers();
			$server_transfers = $server_transfers_query->get_posts();
			foreach($server_transfers as $server_transfer) {
				
				$settings_name = get_post_meta($server_transfer->ID, 'settings_name', true);
				
				if(isset($settings[$settings_name])) {
					$current_setting = $settings[$settings_name];
					
					if($current_setting->qualify_post($post)) {
						$should_transfer = true;
						break;
					}
				}
				
			}
			wp_reset_query();
			
			if($should_transfer) {
				$sync_index_target = get_post_meta($post_id, '_odd_server_transfer_sync_index_target', true);
			
				if($sync_index_target) {
					update_post_meta($post_id, '_odd_server_transfer_sync_index_target', intval($sync_index_target)+1);
				}
				else {
					update_post_meta($post_id, '_odd_server_transfer_sync_index_target', 1);
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
		
		public function transfer_post($post) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::transfer_post<br />");
			
			$settings = $this->get_settings();
			
			$server_transfers_query = $this->get_server_transfers();
			$server_transfers = $server_transfers_query->get_posts();
			foreach($server_transfers as $server_transfer) {
				
				$settings_name = get_post_meta($server_transfer->ID, 'settings_name', true);
				
				if(isset($settings[$settings_name])) {
					$current_setting = $settings[$settings_name];
					
					if($current_setting->qualify_post($post)) {
						$result = $current_setting->transfer_post($post, $server_transfer);
					}
				}
				
			}
			wp_reset_query();
			
			
			return false; //MEDEBUG
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
				
				//var_dump($post);
				
				$is_incoming_link = get_post_meta($post->ID, '_odd_server_transfer_is_incoming', true);
				
				if($is_incoming_link) {
					$module_data = array('id' => $post->ID, 'syncDate' => get_post_meta($post->ID, '_odd_server_transfer_incoming_sync_date', true));
					$this->output_notice('incomingSyncNotice', $module_data);
				}
				else {
					$sync_index = intval(get_post_meta($post->ID, '_odd_server_transfer_sync_index', true));
					$sync_index_target = intval(get_post_meta($post->ID, '_odd_server_transfer_sync_index_target', true));
				
					if($sync_index_target > $sync_index) {
					
						$module_data = array('id' => $post->ID, 'transferUrl' => get_home_url().'/wp-json/odd-site-transfer/v2/post/'.($post->ID).'/transfer');
						$this->output_notice('checkSyncNotice', $module_data);
					}
				}
			}
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\Admin\TransferHooks<br />");
		}
	}
?>