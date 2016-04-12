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
			echo($data);
			
			$return_data_array = json_decode($data);
			
			if($return_data_array->code === 'success') {
				return $return_data_array->data;
			}
			return NULL;
		}
		
		protected function transfer_user($user, $server_transfer_post) {
			echo("\OddSiteTransfer\Admin\TransferHooks::transfer_user<br />");
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'sync/user';
			
			$user_id = $user->ID;
			$user_local_id = get_user_meta($user_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, true);
			var_dump($user);
			
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
			
			
			
			$send_data = array('ids' => $post_ids, 'data' => $post_data);
			$repsonse_data = $this->send_request($url, $send_data);
			var_dump($repsonse_data);
			
			if($repsonse_data) {
				//update_post_meta($post_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, $repsonse_data);
			}
			
			die; //MEDEBUG
		}
		
		protected function transfer_post($post, $server_transfer_post) {
			echo("\OddSiteTransfer\Admin\TransferHooks::transfer_post<br />");
			
			$post_id = $post->ID;
			$post_type = $post->post_type;
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			if($post_type === 'post') { //METODO: make settings for this
				//echo("++++");
				$base_url = get_post_meta($server_transfer_post_id, 'url', true);
				$url = $base_url.'sync/post';
				//echo($base_url);
				
				$local_id = get_post_meta($post_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, true);
				
				$post_ids = array(
					'origin_id' => $post->ID,
					'local_id' => $local_id
				);
				
				$author_id = $post->post_author;
				//METODO: handle no author
				$post_author = get_user_by('id', $author_id);
				$this->transfer_user($post_author, $server_transfer_post);
				$auhtor_local_id = get_user_meta($author_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, true);
				
				$post_data = array(
					'post_type' => $post->post_type,
					'post_status' => $post->post_status,
					'post_title' => $post->post_title,
					'post_content' => $post->post_content
				);
			
				//METODO
				$send_data = array('ids' => $post_ids, 'data' => $post_data);
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