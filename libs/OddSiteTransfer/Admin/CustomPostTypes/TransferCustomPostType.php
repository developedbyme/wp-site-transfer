<?php
	namespace OddSiteTransfer\Admin\CustomPostTypes;
	
	use \OddSiteTransfer\OddCore\Admin\CustomPostTypes\CustomPostTypePost;
	
	// \OddSiteTransfer\Admin\CustomPostTypes\TransferCustomPostType
	class TransferCustomPostType extends CustomPostTypePost {
		
		function __construct() {
			//echo("\Admin\CustomPostTypes\TransferCustomPostType::__construct<br />");
			
			parent::__construct();
			
			$this->set_names('ost_transfer', 'transfer');
			$this->_arguments['supports'] = array('title');
			$this->_arguments['taxonomies'] = array();
			
		}
		
		public static function test_import() {
			echo("Imported \Admin\CustomPostTypes\TransferCustomPostType<br />");
		}
	}
?>