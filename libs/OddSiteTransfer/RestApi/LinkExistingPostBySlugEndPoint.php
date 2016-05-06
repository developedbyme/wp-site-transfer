<?php
	namespace OddSiteTransfer\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	use \OddSiteTransfer\OddCore\Utils\HttpLoading as HttpLoading;
	
	// \OddSiteTransfer\RestApi\LinkExistingPostBySlugEndPoint
	class LinkExistingPostBySlugEndPoint extends EndPoint {
		
		protected $links = array();
		protected $http_log = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\LinkExistingPostBySlugEndPoint::__construct<br />");
			
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
				
				if($repsonse_data['code'] == '200') {
					$data = json_decode($repsonse_data['data']);
					if($data->code === 'success' && $data->data->resultType === 'singlePost') {
						$new_id = $data->data->ids[0];
						update_post_meta($post_id, '_odd_server_transfer_remote_id_'.$server_transfer_post_id, $new_id);
						$this->links[] = array('status' => 'new', 'server' => $server_transfer_post_id, 'localId' => $new_id);
					}
				}
			}
			else {
				$this->links[] = array('status' => 'existing', 'server' => $server_transfer_post_id, 'localId' => $local_id);
			}
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\LinkExistingPostBySlugEndPoint::perform_call<br />");
			
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
			
			return $this->output_success($this->links);
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\LinkExistingPostBySlugEndPoint<br />");
		}
	}
?>