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
		
		protected function create_server_settings() {
			//echo("\OddSiteTransfer\Admin\TransferHooks::create_server_settings<br />");
			$this->settings = array();
			
			//Defaults
			$attachment_post_encoder = EncoderSetup::create_attachment_post_encoder();
			$default_term_encoder = EncoderSetup::create_term_encoder();
			$default_user_encoder = EncoderSetup::create_user_encoder();
			
			$page_post_encoder = EncoderSetup::create_post_encoder(array('page'));
			EncoderSetup::add_meta_fields_to_encoder($page_post_encoder, array('_wp_page_template'), array());
			$post_post_encoder = EncoderSetup::create_post_encoder(array('post'));
			
			//METODO: split this up
			$wine_post_encoder = EncoderSetup::create_post_encoder(array('oa_wine'));
			
			/*
			$wf_wine_post_encoder = EncoderSetup::create_post_encoder(
				array('oa_wine'),
				array(
					array('term' => 'sb-bestallningssortiment', 'taxonomy' => 'dijoy_wine_availability'),
					array('term' => 'sb-fast-sortiment', 'taxonomy' => 'dijoy_wine_availability'),
					array('term' => 'sb-tillfalligt-sortiment-exklusivt', 'taxonomy' => 'dijoy_wine_availability')
				),
				'or'
			);
			
			$enjoy_wine_post_encoder = EncoderSetup::create_targeted_post_encoder(
				array('oa_wine'),
				array(
					array('term' => 'horeca-grossist', 'taxonomy' => 'dijoy_wine_availability'),
					array('term' => 'horeca-restaurant', 'taxonomy' => 'dijoy_wine_availability')
				),
				'or'
			);
			*/
			
			//Fields
			$recipe_meta_data_fields = array(
				'_has_step_instructions',
				'_has_matched_ingredients',
				'show_wine_recommendation_on_zeta',
				'number_of_wine_recommendations',
				'wine_recommendation_1_locked',
				'wine_recommendation_2_locked',
				'wine_recommendation_3_locked',
				'wine_recommendation_4_locked'
			);
			
			$recipe_post_ids_meta_data_fields = array(
				'wine_recommendation_1',
				'wine_recommendation_2',
				'wine_recommendation_3',
				'wine_recommendation_4'
			);
			
			//Foodservice
			$foodservice_recipe_post_encoder = EncoderSetup::create_targeted_post_encoder(
				array('oa_recipe'),
				array(
					array('term' => 'foodservice', 'taxonomy' => 'oa_target_site')
				)
			);
			EncoderSetup::add_meta_fields_to_encoder($foodservice_recipe_post_encoder, $recipe_meta_data_fields, $recipe_post_ids_meta_data_fields);
			
			$foodservice_product_post_encoder = EncoderSetup::create_any_meta_in_acf_list_targeted_post_encoder(
				array('oa_product'),
				array(
					array('list' => 'product_information', 'key' => 'target_catering', 'value' => 'yes')
				)
			);
			
			//Accademia
			$accademia_recipe_post_encoder = EncoderSetup::create_targeted_post_encoder(
				array('oa_recipe'),
				array(
					array('term' => 'accademia', 'taxonomy' => 'oa_target_site')
				)
			);
			EncoderSetup::add_meta_fields_to_encoder($accademia_recipe_post_encoder, $recipe_meta_data_fields, $recipe_post_ids_meta_data_fields);
			
			//Zeta
			$zeta_recipe_post_encoder = EncoderSetup::create_targeted_post_encoder(
				array('oa_recipe'),
				array(
					array('term' => 'zeta', 'taxonomy' => 'oa_target_site')
				)
			);
			EncoderSetup::add_meta_fields_to_encoder($zeta_recipe_post_encoder, $recipe_meta_data_fields, $recipe_post_ids_meta_data_fields);
			
			$zeta_product_post_encoder = EncoderSetup::create_any_meta_in_acf_list_targeted_post_encoder(
				array('oa_product'),
				array(
					array('list' => 'product_information', 'key' => 'target_consumer', 'value' => 'yes'),
					array('list' => 'product_information', 'key' => 'brand', 'value' => 'Zeta')
				),
				'and'
			);
			
			//Wine and friends
			$wf_recipe_post_encoder = EncoderSetup::create_targeted_post_encoder(
				array('oa_recipe'),
				array(
					array('term' => 'wine-and-friends', 'taxonomy' => 'oa_target_site')
				)
			);
			EncoderSetup::add_meta_fields_to_encoder($wf_recipe_post_encoder, $recipe_meta_data_fields, $recipe_post_ids_meta_data_fields);
			
			$wine_producer_post_encoder = EncoderSetup::create_post_encoder(array('oa_wine_producer'));
			
			//--- Settings
			//Default
			$default_server_settings = new \OddSiteTransfer\SiteTransfer\ServerSettings();
			$default_server_settings->add_encoder($attachment_post_encoder);
			$default_server_settings->add_encoder($default_term_encoder);
			$default_server_settings->add_encoder($default_user_encoder);
			$this->add_server_settings('default', $default_server_settings);
			
			//Zeta
			$current_server_settings = new \OddSiteTransfer\SiteTransfer\ServerSettings();
			$current_server_settings->add_encoder($zeta_recipe_post_encoder);
			$current_server_settings->add_encoder($zeta_product_post_encoder);
			$current_server_settings->add_encoder($wine_post_encoder);
			$current_server_settings->add_encoder($attachment_post_encoder);
			$current_server_settings->add_encoder($default_term_encoder);
			$current_server_settings->add_encoder($default_user_encoder);
			$this->add_server_settings('zeta', $current_server_settings);
			
			//Foodservice
			$current_server_settings = new \OddSiteTransfer\SiteTransfer\ServerSettings();
			$current_server_settings->add_encoder($foodservice_recipe_post_encoder);
			//$current_server_settings->add_encoder($foodservice_product_post_encoder);
			$current_server_settings->add_encoder($attachment_post_encoder);
			$current_server_settings->add_encoder($default_term_encoder);
			$current_server_settings->add_encoder($default_user_encoder);
			$this->add_server_settings('foodservice', $current_server_settings);
			
			//Accademia
			$current_server_settings = new \OddSiteTransfer\SiteTransfer\ServerSettings();
			$current_server_settings->add_encoder($accademia_recipe_post_encoder);
			$current_server_settings->add_encoder($attachment_post_encoder);
			$current_server_settings->add_encoder($default_term_encoder);
			$current_server_settings->add_encoder($default_user_encoder);
			$this->add_server_settings('accademia', $current_server_settings);
			
			//Wine & friends
			$current_server_settings = new \OddSiteTransfer\SiteTransfer\ServerSettings();
			$current_server_settings->add_encoder($wf_recipe_post_encoder);
			$current_server_settings->add_encoder($zeta_product_post_encoder); //MEDEBUG
			$current_server_settings->add_encoder($wine_post_encoder);
			$current_server_settings->add_encoder($wine_producer_post_encoder);
			$current_server_settings->add_encoder($attachment_post_encoder);
			$current_server_settings->add_encoder($default_term_encoder);
			$current_server_settings->add_encoder($default_user_encoder);
			$this->add_server_settings('wf', $current_server_settings);
			
			//Enjoy wine
			$current_server_settings = new \OddSiteTransfer\SiteTransfer\ServerSettings();
			$current_server_settings->add_encoder($wine_post_encoder);
			$current_server_settings->add_encoder($wine_producer_post_encoder);
			$current_server_settings->add_encoder($attachment_post_encoder);
			$current_server_settings->add_encoder($default_term_encoder);
			$current_server_settings->add_encoder($default_user_encoder);
			$this->add_server_settings('enjoy', $current_server_settings);
			
			//Manual post transfer
			$current_server_settings = new \OddSiteTransfer\SiteTransfer\ServerSettings();
			$current_server_settings->add_encoder($page_post_encoder);
			$current_server_settings->add_encoder($post_post_encoder);
			$current_server_settings->add_encoder($attachment_post_encoder);
			$current_server_settings->add_encoder($default_term_encoder);
			$current_server_settings->add_encoder($default_user_encoder);
			$this->add_server_settings('posts', $current_server_settings);
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
		
		public function transfer_post($post, $force_dependencies_transfer_steps = 0) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::transfer_post<br />");
			
			//var_dump($force_dependencies_transfer_steps);
			
			$return_data = array();
			
			$settings = $this->get_settings();
			
			$server_transfers_query = $this->get_server_transfers();
			$server_transfers = $server_transfers_query->get_posts();
			foreach($server_transfers as $server_transfer) {
				
				$settings_name = get_post_meta($server_transfer->ID, 'settings_name', true);
				
				$current_transfer_return_data = array();
				$current_transfer_return_data['name'] = $server_transfer->post_title;
				
				if(isset($settings[$settings_name])) {
					$current_setting = $settings[$settings_name];
					
					if($current_setting->qualify_post($post)) {
						$result = $current_setting->transfer_post($post, $server_transfer, $force_dependencies_transfer_steps);
						if($result) {
							if($current_setting->has_error()) {
								$current_transfer_return_data['status'] = 'error';
								$current_transfer_return_data['code'] = 'logged-error';
							}
							else {
								$current_transfer_return_data['status'] = 'sent';
								$current_transfer_return_data['code'] = 'sent-'.$result['transfer_type'];
							}
							if(isset($result['url'])) {
								$current_transfer_return_data['url'] = $result['url'];
							}
						}
						else {
							$current_transfer_return_data['status'] = 'error';
							$current_transfer_return_data['code'] = 'unknown-error';
						}
						$current_transfer_return_data['result'] = $current_setting->get_result();
						$current_setting->reset_log();
					}
					else {
						$current_transfer_return_data['status'] = 'ignored';
						$current_transfer_return_data['code'] = 'not-qualified';
						$current_transfer_return_data['message'] = 'Post doesn\'t qualify for transfer.';
					}
				}
				else {
					$current_transfer_return_data['status'] = 'ignored';
					$current_transfer_return_data['code'] = 'missing-setting';
					$current_transfer_return_data['message'] = 'Setting '.$settings_name.' doesn\'t exist.';
				}
				
				$return_data[] = $current_transfer_return_data;
			}
			wp_reset_query();
			
			
			return $return_data;
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
		
		public function remove_missing_posts($post_type) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::remove_missing_posts<br />");
			
			$args = array(
				'post_type'  => $post_type,
				'posts_per_page' => -1,
				'fields' => 'ids'
			);
			
			$query = new WP_Query( $args );
			
			$transfer_ids = array();
			
			foreach($query->posts as $id) {
				$transfer_id = get_post_meta($id, '_odd_server_transfer_id', true);
				if($transfer_id !== '') {
					$transfer_ids[] = $transfer_id;
				}
			}
			
			$return_data = array();
			$settings = $this->get_settings();
			
			$server_transfers_query = $this->get_server_transfers();
			$server_transfers = $server_transfers_query->get_posts();
			foreach($server_transfers as $server_transfer) {
				
				$settings_name = get_post_meta($server_transfer->ID, 'settings_name', true);
				
				$current_transfer_return_data = array();
				$current_transfer_return_data['name'] = $server_transfer->post_title;
				
				if(isset($settings[$settings_name])) {
					$current_setting = $settings[$settings_name];
					$current_setting->transfer_missing_posts($post_type, $transfer_ids, $server_transfer);
					$current_transfer_return_data['status'] = 'sent';
					$current_transfer_return_data['result'] = $current_setting->get_result();
				}
				else {
					$current_transfer_return_data['status'] = 'missing_setting';
					$current_transfer_return_data['message'] = 'Setting '.$settings_name.' doesn\'t exist.';
				}
				
				$return_data[] = $current_transfer_return_data;
			}
			
			return $return_data;
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
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\Admin\TransferHooks<br />");
		}
	}
?>