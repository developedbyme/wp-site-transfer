<?php

	namespace OddSiteTransfer\RestApi\BidirectionalTransfer;
	
	use \WP_Query;
	use \OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\BidirectionalTransfer\TransferDataEndpoint
	class TransferDataEndpoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\BidirectionalTransfer\TransferDataEndpoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\BidirectionalTransfer\TransferDataEndpoint::perform_call<br />");
			
			$transfer_id = $data['id'];
			
			$transfer_post_id = ost_get_transfer_post_id($transfer_id);
			
			if($transfer_post_id === -1) {
				return $this->output_error("No transfer for id ".$transfer_id);
			}
			
			$post = get_post($transfer_post_id);
			
			$transfer_id = get_post_meta($transfer_post_id, 'ost_id', true);
			$type = get_post_meta($transfer_post_id, 'ost_transfer_type', true);
			$data = get_post_meta($transfer_post_id, 'ost_encoded_data', true);
			
			$return_data = array(
				'id' => $transfer_post_id,
				'type' => $type,
				'name' => $post->post_title,
				'data' => $data,
				'hash' => get_post_meta($transfer_post_id, 'ost_encoded_data_hash', true)
			);
			
			return $this->output_success($return_data);
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\BidirectionalTransfer\TransferDataEndpoint<br />");
		}
	}
?>