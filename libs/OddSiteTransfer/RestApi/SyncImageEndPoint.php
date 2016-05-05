<?php
	namespace OddSiteTransfer\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\RestApi\SyncImageEndPoint
	class SyncImageEndPoint extends EndPoint {
		
		function __construct() {
			//echo("\OddSiteTransfer\RestApi\SyncImageEndPoint::__construct<br />");
			
			
		}
		
		public function perform_call($data) {
			//echo("\OddSiteTransfer\RestApi\SyncImageEndPoint::perform_call<br />");
			
			ini_set('memory_limit', '512M');
			
			$path = $data['path'];
			$data = $data['data'];
			
			$upload_dir = wp_upload_dir();
			
			$file_to_save = $upload_dir['basedir'].'/'.$path;
			
			$encoded_data = base64_decode($data);
			
			$save_result = file_put_contents($file_to_save, $encoded_data);
			
			if($save_result) {
				return $this->output_success($upload_dir['baseurl'].'/'.$path);
			}
			return $this->output_error("Couldn't save file.");
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\SyncImageEndPoint<br />");
		}
	}
?>