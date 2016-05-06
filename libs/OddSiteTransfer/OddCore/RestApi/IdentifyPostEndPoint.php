<?php
	namespace OddSiteTransfer\OddCore\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\OddCore\RestApi\IdentifyPostEndPoint
	class IdentifyPostEndPoint extends EndPoint {
		
		protected $_arguments = array();
		
		function __construct() {
			//echo("\OddCore\RestApi\IdentifyPostEndPoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddCore\RestApi\IdentifyPostEndPoint::perform_call<br />");
			
			$post_type = $data['postType'];
			$serach_type = $data['searchType'];
			$identifier = $data['identifier'];
			
			$arguments = array(
				'post_type' => $post_type
			);
			
			switch($serach_type) {
				case 'slug':
					$arguments['name'] = $identifier;
					break;
				default:
					return $this->output_error('Unknown search type '.$serach_type);
			}
			
			$result_type = 'noPosts';
			$return_array = array();
			
			$identification_query = new WP_Query($arguments);
			if($identification_query->have_posts()) {
				$posts = $identification_query->get_posts();
				
				if(count($posts)) {
					$result_type = 'singlePost';
				}
				else {
					$result_type = 'mulitplePosts';
				}
				foreach($posts as $post) {
					$return_array[] = $post->ID;
				}
			}
			
			return $this->output_success(array('resultType' => $result_type, 'ids' => $return_array));
		}
		
		public static function test_import() {
			echo("Imported \OddCore\RestApi\IdentifyPostEndPoint<br />");
		}
	}
?>