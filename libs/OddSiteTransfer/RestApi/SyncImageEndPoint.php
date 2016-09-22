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
			
			switch( $_FILES['file']['error'] ) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					return $this->output_error("File size is too big (max size: ".get_max_upload().").");
				case UPLOAD_ERR_PARTIAL:
					return $this->output_error("File upload was partial.");
				case UPLOAD_ERR_NO_FILE:
					return $this->output_error("Zero length file was uploaded.");
				default:
					return $this->output_error("Unknown upload error (code: ".$_FILES['file']['error'].").");
			}
			
			$path = $data['path'];
			
			$upload_dir = wp_upload_dir();
			
			$file_to_save = $upload_dir['basedir'].'/'.$path;
			
			$parent_directory = dirname($file_to_save);
			
			if (!file_exists($parent_directory)) {
				mkdir($parent_directory, 0755, true);
			}
			
			if(move_uploaded_file($_FILES['file']['tmp_name'], $file_to_save)) {
				return $this->output_success($upload_dir['baseurl'].'/'.$path);
			}
			return $this->output_error("Couldn't save file (".($_FILES['file']['tmp_name'])." to ".$file_to_save.").");
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\RestApi\SyncImageEndPoint<br />");
		}
	}
?>