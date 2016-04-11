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
		
		public function hook_save_post($post_id, $post, $update) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_save_post<br />");
			
			if(wp_is_post_revision($post_id)) {
				return;
			}
			
			remove_action('save_post', array($this, 'hook_save_post'));
			
			$current_page_name = $post->post_type;
			
			$args = array(
				'post_type' => 'server-transfer',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'caller_get_posts'=> 1
			);
			
			$post_type = $post->post_type;
			//echo($post_type);
			
			$server_transfers_query = new WP_Query($args);
			$server_transfers = $server_transfers_query->get_posts();
			foreach($server_transfers as $server_transfer) {
				$server_transfer_post_id = $server_transfer->ID;
				
				if($post_type === 'post') { //METODO: make settings for this
					//echo("++++");
					$base_url = get_post_meta($server_transfer_post_id, 'url', true);
					//echo($base_url);
					
					$local_id = get_post_meta($post_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, true);
					
					$post_ids = array(
						'origin_id' => $post->ID,
						'local_id' => $local_id
					);
				
					$post_data = array(
						'post_type' => $post->post_type,
						'post_status' => $post->post_status,
						'post_title' => $post->post_title,
						'post_content' => $post->post_content
					);
				
					//METODO
					$url = $base_url.'sync/post';
				
					$fields_string = http_build_query(array('ids' => $post_ids, 'data' => $post_data));
					
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
				
					echo($httpcode);
					echo($data);
					
					$return_data_array = json_decode($data);
					
					var_dump($return_data_array);
					echo($return_data_array->code);
					
					if($return_data_array->code === 'success') {
						$remote_id = $return_data_array->data;
						echo($remote_id);
						add_post_meta($post_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, $remote_id, true);
					}
					
					
					
					die; //MEDEBUG
				}
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