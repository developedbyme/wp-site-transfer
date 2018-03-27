<?php
	namespace OddSiteTransfer\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\CreateTransferForPostEndpoint
	class CreateTransferForPostEndpoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\CreateTransferForPostEndpoint::__construct<br />");
			
			
		}
		
		protected function get_post_transfer_id($post) {
			$id = get_post_meta($post->ID, 'ost_transfer_id', true);
			if(!$id) {
				$id = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
				update_post_meta($post->ID, 'ost_transfer_id', $id);
			}
			
			return $id;
		}
		
		protected function get_transfer_post_id($transfer_id) {
			
			$args = array(
				'post_type' => 'ost_transfer',
				'fields' => 'ids',
				'meta_query' => array(
					array(
						'key' => 'ost_id',
						'value' => $transfer_id,
						'compare' => '='
					)
				)
			);
			
			$posts = get_posts($args);
			
			if(empty($posts)) {
				return -1;
			}
			
			return $posts[0];
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\CreateTransferForPostEndpoint::perform_call<br />");
			
			//$plugin = \OddSiteTransfer\Plugin::$singleton;
			
			$post_id = $data['id'];
			$post = get_post($post_id);
			
			if(!$post) {
				return $this->output_error($post_id.' is not a post.');
			}
			
			$transfer_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/post_transfer_type', null, $post_id, $post);
			
			if(!$transfer_type) {
				return $this->output_success(array('type' => 'no-transfer-type'));
			}
			
			$transfer_id = $this->get_post_transfer_id($post);
			$transfer_post_id = $this->get_transfer_post_id($transfer_id);
			
			if($transfer_post_id === -1) {
				$args = array(
					'post_type' => 'ost_transfer',
					'post_status' => 'draft',
					'post_title' => $transfer_type.' - '.($post->post_title)
				);
				
				$transfer_post_id = wp_insert_post($args);
			
				if(!$transfer_post_id) {
					return $this->output_error('Error creating post');
				}
				
				update_post_meta($transfer_post_id, 'ost_id', $transfer_id);
				update_post_meta($transfer_post_id, 'ost_transfer_type', $transfer_type);
				update_post_meta($transfer_post_id, 'ost_transfer_status', 0);
				
				$encoder = new \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder();
				
				$encoded_data = $encoder->encode($post);
				$encoded_data_hash = md5(serialize($encoded_data));
				
				update_post_meta($transfer_post_id, 'ost_encoded_data', $encoded_data);
				update_post_meta($transfer_post_id, 'ost_encoded_data_hash', $encoded_data_hash);
			
				$publish_ids = array();
			
				$publish_ids[] = $transfer_post_id;
				
				foreach($publish_ids as $publish_id) {
					wp_update_post(array('ID' => $publish_id, 'post_status' => 'publish'));
				}
			}
			
			return $this->output_success(array('id' => $transfer_post_id, 'transferId' => $transfer_id));
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\CreateTransferForPostEndpoint<br />");
		}
	}
?>