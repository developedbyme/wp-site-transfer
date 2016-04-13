<?php
	namespace OddSiteTransfer\Admin;
	
	use \WP_Query;
	
	// \OddSiteTransfer\Admin\TransferHooks
	class TransferHooks {
		
		
		
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
		}
		
		protected function send_request($url, $data) {
			
			$fields_string = http_build_query($data);
			//echo($fields_string."<br />");
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
			//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			$data = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
		
			//echo($httpcode);
			//echo($data);
			
			$return_data_array = json_decode($data);
			
			if($return_data_array->code === 'success') {
				return $return_data_array->data;
			}
			return NULL;
		}
		
		protected function transfer_user($user, $server_transfer_post) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::transfer_user<br />");
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'sync/user';
			
			$user_id = $user->ID;
			$user_local_id = get_user_meta($user_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, true);
			
			
			$user_ids = array(
				'origin_id' => $user_id,
				'local_id' => $user_local_id
			);
			
			$user_data = array(
				'user_login' => $user->user_login,
				'user_nicename' => $user->user_nicename,
				'user_email' => $user->user_email,
				'display_name' => $user->display_name,
				'first_name' => $user->first_name,
				'last_name' => $user->last_name
			);
			
			$send_data = array('ids' => $user_ids, 'data' => $user_data);
			$repsonse_data = $this->send_request($url, $send_data);
			
			if($repsonse_data) {
				update_user_meta($user_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, $repsonse_data);
			}
		}
		
		protected function encode_acf_field($acf_field, $post_id, $server_transfer_post, $override_value = NULL) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::encode_acf_field<br />");
			//echo($acf_field['type']."<br />");
			
			$current_send_field = NULL;
			
			switch($acf_field['type']) {
				case "repeater":
					$rows_array = array();
					$current_key = $acf_field['key'];
					if(have_rows($current_key, $post_id)) {
						while(have_rows($current_key, $post_id)) {
							
							the_row();
							$current_row = get_row();
							
							$row_result = array();
							
							foreach($current_row as $key => $value) {
								$current_row_field = get_field_object($key, $post_id, false, true);
								$row_result[$current_row_field['name']] = $this->encode_acf_field($current_row_field, $post_id, $server_transfer_post, $value);
							}
							
							array_push($rows_array, $row_result);
						}
					}
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => $rows_array
					);
					break;
				case "post_object":
					$linked_post_ids = $override_value ? $override_value : $acf_field['value'];
					$linked_post_local_ids = array();
					foreach($linked_post_ids as $linked_post_id) {
						$linked_post_local_ids[] = $this->get_local_post_id($linked_post_id, $server_transfer_post->ID);
					}
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => $linked_post_local_ids
					);
					break;
				break;
				default:
					echo("Unknown type: ".$acf_field['type']."<br />");
					var_dump($acf_field);
				case "radio":
				case "textarea":
				case "url":
				case "text":
				case "number":
					$current_send_field = array(
						'type' => $acf_field['type'],
						'value' => $acf_field['value']
					);
					if($override_value) {
						$current_send_field['value'] = $override_value;
					}
					break;
			}
			
			//echo("// \OddSiteTransfer\Admin\TransferHooks::encode_acf_field<br />");
			return $current_send_field;
		}
		
		protected function get_local_post_id($post_id, $server_transfer_post_id) {
			return get_post_meta($post_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, true);
		}
		
		protected function transfer_post($post, $server_transfer_post) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::transfer_post<br />");
			
			$post_id = $post->ID;
			$post_type = $post->post_type;
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			if($post_type === 'post' || $post_type === 'oa_recipe' || $post_type === 'oa_product') { //METODO: make settings for this
				//echo("++++");
				$base_url = get_post_meta($server_transfer_post_id, 'url', true);
				$url = $base_url.'sync/post';
				//echo($base_url);
				
				$local_id = $this->get_local_post_id($post_id, $server_transfer_post_id);
				
				$post_ids = array(
					'origin_id' => $post->ID,
					'local_id' => $local_id
				);
				
				$author_id = $post->post_author;
				//METODO: handle no author
				$post_author = get_user_by('id', $author_id);
				$this->transfer_user($post_author, $server_transfer_post);
				$author_local_id = get_user_meta($author_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, true);
				
				$post_data = array(
					'post_type' => $post->post_type,
					'post_status' => $post->post_status,
					'post_title' => $post->post_title,
					'post_content' => $post->post_content,
					'post_author' => $author_local_id,
					'post_name' => $post->post_name,
					'post_date' => $post->post_date,
					'post_date_gmt' => $post->post_date_gmt,
					'post_modified' => $post->post_modified,
					'post_modified_gmt' => $post->post_modified_gmt,
					'comment_status' => $post->comment_status,
					'menu_order' => $post->menu_order,
					'post_mime_type' => $post->post_mime_type
				);
				
				$meta_data = array();
				
				if($post_type === 'oa_recipe') {
					
					$send_fields = array();
					
					$acf_fields = get_field_objects($post_id);
					
					foreach($acf_fields as $name => $acf_field) {
						$send_fields[$name] = $this->encode_acf_field($acf_field, $post_id, $server_transfer_post);
					}
					
					$meta_data['acf'] = $send_fields;
				}
				
				//METODO
				$send_data = array('ids' => $post_ids, 'data' => $post_data, 'meta_data' => $meta_data);
				$repsonse_data = $this->send_request($url, $send_data);
				
				if($repsonse_data) {
					update_post_meta($post_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, $repsonse_data);
				}
			}
		}
		
		public function hook_save_post($post_id, $post, $update) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_save_post<br />");
			
			if(wp_is_post_revision($post_id)) {
				return;
			}
			
			remove_action('save_post', array($this, 'hook_save_post'));
			
			$args = array(
				'post_type' => 'server-transfer',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'caller_get_posts'=> 1
			);
			
			$server_transfers_query = new WP_Query($args);
			$server_transfers = $server_transfers_query->get_posts();
			foreach($server_transfers as $server_transfer) {
				
				$this->transfer_post($post, $server_transfer);
				
			}
			wp_reset_query();
		}
		
		public function hook_created_term($term_id, $tt_id, $taxonomy) {
			echo("\OddSiteTransfer\Admin\TransferHooks::hook_created_term<br />");
			
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
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\Admin\TransferHooks<br />");
		}
	}
?>