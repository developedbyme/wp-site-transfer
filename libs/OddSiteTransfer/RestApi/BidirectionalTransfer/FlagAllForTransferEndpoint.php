<?php

	namespace OddSiteTransfer\RestApi\BidirectionalTransfer;
	
	use \WP_Query;
	use \OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\BidirectionalTransfer\FlagAllForTransferEndpoint
	class FlagAllForTransferEndpoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\BidirectionalTransfer\FlagAllForTransferEndpoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\BidirectionalTransfer\FlagAllForTransferEndpoint::perform_call<br />");
			
			$transfer_type = $data['transfer_type'];
			
			$arguments = array(
				'post_type' => 'ost_transfer',
				'fields' => 'ids',
				'meta_query' => array(
					array(
						'key' => 'ost_transfer_type',
						'value' => $transfer_type,
						'compare' => '=',
					),
				)
			);
			
			$ids = get_posts($arguments);
			
			if($ids) {
				foreach($ids as $post_id) {
					update_post_meta($post_id, 'ost_transfer_status', 0);
				}
			}
			
			return $this->output_success(count($ids));
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\BidirectionalTransfer\FlagAllForTransferEndpoint<br />");
		}
	}
?>