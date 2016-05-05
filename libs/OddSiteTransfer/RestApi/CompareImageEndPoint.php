<?php
	namespace OddSiteTransfer\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\CompareImageEndPoint
	class CompareImageEndPoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\CompareImageEndPoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\CompareImageEndPoint::perform_call<br />");
			
			$path = $data['path'];
			$size = intval($data['size']);
			
			$upload_dir = wp_upload_dir();
			
			$file_to_save = $upload_dir['basedir'].'/'.$path;
			
			$return_array = array();
			$return_array['testSize'] = $size;
			
			$file_exists = file_exists($file_to_save);
			if($file_exists) {
				$file_size = filesize($file_to_save);
				$file_exists = $file_exists && $file_size === $size;
				$return_array['actualSize'] = $file_size;
			}
			
			$return_array['match'] = $file_exists;
			
			return $this->output_success($return_array);
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\CompareImageEndPoint<br />");
		}
	}
?>