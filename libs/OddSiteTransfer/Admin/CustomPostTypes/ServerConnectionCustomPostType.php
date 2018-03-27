<?php
	namespace OddSiteTransfer\Admin\CustomPostTypes;
	
	use \OddSiteTransfer\OddCore\Admin\CustomPostTypes\CustomPostTypePost;
	
	// \OddSiteTransfer\Admin\CustomPostTypes\ServerConnectionCustomPostType
	class ServerConnectionCustomPostType extends CustomPostTypePost {
		
		function __construct() {
			//echo("\Admin\CustomPostTypes\ServerConnectionCustomPostType::__construct<br />");
			
			parent::__construct();
			
			$this->set_names('server-connection', 'server connection');
			$this->_arguments['supports'] = array('title');
			
			$current_box = new \OddSiteTransfer\OddCore\Admin\MetaData\PostMetaDataFieldBox();
			$current_box->set_name('URL');
			$current_box->set_meta_key('url');
			$this->add_meta_box_after_title($current_box);
			
			$current_box = new \OddSiteTransfer\OddCore\Admin\MetaData\PostMetaDataBox();
			$current_box->set_name('Settings');
			$current_box->set_nonce_name('settings_nonce');
			
			$select_field = new \OddSiteTransfer\OddCore\Admin\MetaData\SelectMetaField();
			$select_field->set_name('settings_name');
			$select_field->set_default_value('default');
			$select_field->add_option('Default', 'default');
			$current_box->add_meta_field($select_field);
			
			$this->add_meta_box_after_title($current_box);
			
		}
		
		public static function test_import() {
			echo("Imported \Admin\CustomPostTypes\ServerConnectionCustomPostType<br />");
		}
	}
?>