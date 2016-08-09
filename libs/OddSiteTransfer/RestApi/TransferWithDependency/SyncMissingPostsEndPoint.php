<?php
	namespace OddSiteTransfer\RestApi\TransferWithDependency;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\TransferWithDependency\SyncMissingPostsEndPoint
	class SyncMissingPostsEndPoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\SyncMissingPostsEndPoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\TransferWithDependency\SyncMissingPostsEndPoint::perform_call<br />");
			
			//var_dump($data);
			$allowed_types = array('oa_recipe', 'oa_wine', 'oa_wine_producer');
			
			$post_type = $data['post_type'];
			$existing_ids = $data['existing_ids'];
			
			if(!in_array($post_type, $allowed_types)) {
				return $this->output_error('Type not allowed');
			}
			
			$removed_ids = array();
			
			$args = array(
				'post_type'  => $post_type,
				'posts_per_page' => -1,
				'fields' => 'ids',
				
				'meta_key'     => '_odd_server_transfer_id',
				'meta_value'   => $existing_ids,
				'meta_compare' => 'NOT IN',
			);
			$query = new WP_Query( $args );
			
			foreach($query->posts as $remove_id) {
				$removed_ids[] = $remove_id;
				wp_trash_post($remove_id);
			}
			
			$args = array(
				'post_type'  => $post_type,
				'posts_per_page' => -1,
				'fields' => 'ids',
				
				'meta_key' => '_odd_server_transfer_id',
				'meta_value' => '',
				'meta_compare' => 'NOT EXISTS',
			);
			$query = new WP_Query( $args );
			
			foreach($query->posts as $remove_id) {
				$removed_ids[] = $remove_id;
				wp_trash_post($remove_id);
			}
			
			return $this->output_success(array('removed' => $removed_ids));
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\TransferWithDependency\SyncMissingPostsEndPoint<br />");
		}
	}
?>