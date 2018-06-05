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
			$this->set_argument('public', false);
			$this->set_argument('publicly_queryable', false);
			$this->set_argument('exclude_from_search', true);
			$this->set_argument('has_archive', false);
			
		}
		
		public static function test_import() {
			echo("Imported \Admin\CustomPostTypes\TransferCustomPostType<br />");
		}
	}
?>