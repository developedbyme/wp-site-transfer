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
			
			$upload_dir = wp_upload_dir();
			
			$file_to_save = $upload_dir['basedir'].'/'.$path;
			
			if(move_uploaded_file($_FILES['file']['tmp_name'], $file_to_save)) {
				return $this->output_success($upload_dir['baseurl'].'/'.$path);
			}
			return $this->output_error("Couldn't save file.");
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\SyncImageEndPoint<br />");
		}
	}
?>