<?php
	namespace OddSiteTransfer\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\TransferPostEndPoint
	class TransferPostEndPoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\TransferPostEndPoint::__construct<br />");
			
			
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
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
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
		
		protected function transfer_term($term, $server_transfer_post) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::transfer_term<br />");
			//var_dump($term);
			//echo('<br /><br />');
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'sync/term';
			
			$term_id = $term->term_id;
			$term_local_id = get_term_meta($term_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, true);
			
			$term_ids = array(
				'origin_id' => $term_id,
				'local_id' => $term_local_id
			);
			
			//var_dump($term_ids);
			//echo('<br /><br />');
			
			//METODO: do parent
			
			$term_data = array(
				'name' => $term->name,
				'slug' => $term->slug,
				'description' => $term->description,
				'taxonomy' => $term->taxonomy
			);
			
			$send_data = array('ids' => $term_ids, 'data' => $term_data, 'taxonomy' => $term->taxonomy);
			
			//var_dump($send_data);
			//echo('<br /><br />');
			
			$repsonse_data = $this->send_request($url, $send_data);
			if($repsonse_data) {
				update_term_meta($term_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, $repsonse_data);
			}
		}
		
		protected function transfer_media($media, $server_transfer_post) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::transfer_media<br />");
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			//METODO: check that image needs to be sent
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'sync/image';
			
			$file_path = get_post_meta($media->ID, '_wp_attached_file', true);
			
			$file_to_load = wp_upload_dir()['basedir'].'/'.$file_path;
			
			$file_data = file_get_contents($file_to_load);
			$encoded_file_data = base64_encode($file_data);
			
			$send_data = array('path' => $file_path, 'data' => $encoded_file_data);
			
			$repsonse_data = $this->send_request($url, $send_data);
			if($repsonse_data) {
				//METODO: check that the image is sent
			}
			
			$this->transfer_post($media, $server_transfer_post);
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
			//var_dump($post);
			//echo('<br /><br />');
			
			$post_id = $post->ID;
			$post_type = $post->post_type;
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			if($post_type === 'post' || $post_type === 'oa_recipe' || $post_type === 'oa_product' || $post_type === 'attachment') { //METODO: make settings for this
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
				$meta_data['meta'] = array();
				
				if($post_type === 'oa_recipe') {
					
					$send_fields = array();
					
					setup_postdata($post); 
					$acf_fields = get_field_objects($post_id);
					
					foreach($acf_fields as $name => $acf_field) {
						$send_fields[$name] = $this->encode_acf_field($acf_field, $post_id, $server_transfer_post);
					}
					wp_reset_postdata();
					
					$meta_data['acf'] = $send_fields;
				}
				
				if($post_type !== 'attachment') {
					$media_post_id = get_post_thumbnail_id($post_id);
					
					if($media_post_id) {
						$media_post = get_post($media_post_id);
						
						$this->transfer_media($media_post, $server_transfer_post);
					}
					
					$local_thumbnail_id = get_post_meta($media_post_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, true);
					$meta_data['post_thumbnail_id'] = $local_thumbnail_id;
				}
				else {
					//METODO: should caption be here?
					$meta_data['meta']['_wp_attached_file'] = get_post_meta($post_id, '_wp_attached_file', true);
					$meta_data['meta']['_wp_attachment_metadata'] = get_post_meta($post_id, '_wp_attachment_metadata', true);
					$meta_data['meta']['_wp_attachment_image_alt'] = get_post_meta($post_id, '_wp_attachment_image_alt', true);
				}
				
				$taxonomies = array_keys(get_the_taxonomies($post_id));
				
				$term_data_array = array();
				foreach($taxonomies as $taxonomy) {
					$current_terms = get_the_terms($post_id, $taxonomy);
					//var_dump($current_terms);
					//echo('<br /><br />');
					$local_term_ids = array();
					
					foreach($current_terms as $current_term) {
						$this->transfer_term($current_term, $server_transfer_post);
						$local_term_ids[] = get_term_meta($current_term->term_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, true);
					}
					
					$term_data_array[$taxonomy] = $local_term_ids;
					
				}
				
				//METODO
				$send_data = array('ids' => $post_ids, 'data' => $post_data, 'meta_data' => $meta_data, 'taxonomies' => $term_data_array);
				$repsonse_data = $this->send_request($url, $send_data);
				
				if($repsonse_data) {
					update_post_meta($post_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, $repsonse_data);
				}
			}
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\TransferPostEndPoint::perform_call<br />");
			
			$post_id = $data['id'];
			$post = get_post($post_id);
			
			$sync_index = intval(get_post_meta($post_id, '_odd_server_transfer_sync_index', true));
			$sync_index_target = intval(get_post_meta($post_id, '_odd_server_transfer_sync_index_target', true));
			
			if($sync_index_target === $sync_index) {
				return $this->output_success(array('target' => $sync_index_target, 'index' => $sync_index));
			}
			
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
			
			$sync_index = min($sync_index+1, $sync_index_target);
			
			update_post_meta($post_id, '_odd_server_transfer_sync_index', $sync_index);
			
			return $this->output_success(array('target' => $sync_index_target, 'index' => $sync_index));
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\TransferPostEndPoint<br />");
		}
	}
?>