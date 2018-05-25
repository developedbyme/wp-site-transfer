<?php
	namespace OddSiteTransfer\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\UpdateTransferForPostEndpoint
	class UpdateTransferForPostEndpoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\UpdateTransferForPostEndpoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\UpdateTransferForPostEndpoint::perform_call<br />");
			
			//$plugin = \OddSiteTransfer\Plugin::$singleton;
			
			$post_id = $data['id'];
			$post = get_post($post_id);
			
			if(!$post) {
				return $this->output_error($post_id.' is not a post.');
			}
			
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
			else {
				return $this->output_error('Post doesn\'t have a transfer.');
			}
			
			return $this->output_success(array('id' => $transfer_post_id, 'transferId' => $transfer_id));
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\UpdateTransferForPostEndpoint<br />");
		}
	}
?>