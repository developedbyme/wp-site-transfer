<?php
	namespace OddSiteTransfer\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	use \OddSiteTransfer\OddCore\Utils\HttpLoading as HttpLoading;
	
	// \OddSiteTransfer\RestApi\IdentifyPostEndPoint
	class IdentifyPostEndPoint extends EndPoint {
		
		protected $http_log = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\IdentifyPostEndPoint::__construct<br />");
			
			
		}
		
		protected function send_request_with_file($url, $data) {
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			$data = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			/*
			echo($url);
			echo("\n");
			echo($httpcode);
			echo("\n");
			echo($data);
			echo("\n\n");
			*/
			
			$this->http_log[] = array('url' => $url, 'code' => $httpcode, 'data' => $data);
			
			$return_data_array = json_decode($data);
			
			if($return_data_array->code === 'success') {
				return $return_data_array->data;
			}
			return NULL;
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
			
			/*
			echo($url);
			echo("\n");
			echo($httpcode);
			echo("\n");
			echo($data);
			echo("\n\n");
			*/
			
			$this->http_log[] = array('url' => $url, 'code' => $httpcode, 'data' => $data);
			
			$return_data_array = json_decode($data);
			
			if($return_data_array->code === 'success') {
				return $return_data_array->data;
			}
			return NULL;
		}
		
		protected function get_local_post_id($post_id, $server_transfer_post_id) {
			return get_post_meta($post_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, true);
		}
		
		protected function link_post($post, $server_transfer_post) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::transfer_post<br />");
			//var_dump($post);
			//echo('<br /><br />');
			
			$post_id = $post->ID;
			$post_type = $post->post_type;
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			
			$local_id = $this->get_local_post_id($post_id, $server_transfer_post_id);
			
			if(!$local_id) {
				
				$base_url = get_post_meta($server_transfer_post_id, 'url', true);
				$url = $base_url.'identify/'.$post_type."/slug/".($post->post_name);
				
				$repsonse_data = HttpLoading::load($url);
				
				var_dump($repsonse_data);
				//update_post_meta($post_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, $repsonse_data);
			}
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\IdentifyPostEndPoint::perform_call<br />");
			
			$post_id = $data['id'];
			$post = get_post($post_id);
			
			$args = array(
				'post_type' => 'server-transfer',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'caller_get_posts'=> 1
			);
			
			$server_transfers_query = new WP_Query($args);
			$server_transfers = $server_transfers_query->get_posts();
			foreach($server_transfers as $server_transfer) {
				
				$this->link_post($post, $server_transfer);
				
			}
			wp_reset_query();
			
			return $this->output_success(null);
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\IdentifyPostEndPoint<br />");
		}
	}
?>