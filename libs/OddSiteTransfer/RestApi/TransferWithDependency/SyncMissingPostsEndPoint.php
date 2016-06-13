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
			
			$post_type = $data['post_type'];
			$existing_ids = $data['existing_ids'];
			
			$args = array(
				'post_type'  => $post_type,
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key'     => '_odd_server_transfer_id',
						'value'   => $existing_ids,
						'compare' => 'NOT IN',
					)
				),
			);
			$query = new WP_Query( $args );
			
			$removed_ids = array();
			
			$posts_to_remove = $query->get_posts();
			foreach($posts_to_remove as $post_to_remove) {
				$removed_ids[] = $post_to_remove->ID;
				wp_trash_post($post_to_remove->ID);
			}
			
			return $this->output_success(array('removed' => $removed_ids));
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\TransferWithDependency\SyncMissingPostsEndPoint<br />");
		}
	}
?>