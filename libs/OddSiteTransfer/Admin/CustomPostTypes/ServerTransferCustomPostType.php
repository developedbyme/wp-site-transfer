<?php
	namespace OddSiteTransfer\Admin\CustomPostTypes;
	
	use \OddSiteTransfer\OddCore\Admin\CustomPostTypes\CustomPostTypePost;
	
	// \OddSiteTransfer\Admin\CustomPostTypes\ServerTransferCustomPostType
	class ServerTransferCustomPostType extends CustomPostTypePost {
		
		function __construct() {
			//echo("\Admin\CustomPostTypes\ServerTransferCustomPostType::__construct<br />");
			
			parent::__construct();
			
			$this->set_names('server-transfer', 'server transfer');
			$this->_arguments['supports'] = array('title');
			
			//$this->add_css('wallpaper-ad', plugins_url('assets/css/admin/wallpaper-ad.css', BTDM_PARALLAX_ADS_MAIN_FILE));
			
			//$this->add_javascript('react-with-addons', plugins_url('assets/js/libs/react-with-addons.js', BTDM_PARALLAX_ADS_MAIN_FILE));
			//$this->add_javascript('react-dom', plugins_url('assets/js/libs/react-dom.js', BTDM_PARALLAX_ADS_MAIN_FILE));
			//$this->add_javascript('wallpaper-ad', plugins_url('assets/js/admin/wallpaper-ad.js', BTDM_PARALLAX_ADS_MAIN_FILE));
			
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
			$select_field->add_option('Zeta', 'zeta');
			$select_field->add_option('Wine & Friends', 'wf');
			$select_field->add_option('Enjoy wine', 'enjoy');
			$select_field->add_option('Foodservice', 'foodservice');
			$select_field->add_option('Accademia', 'accademia');
			$select_field->add_option('Manual posts and pages', 'posts');
			$current_box->add_meta_field($select_field);
			
			$this->add_meta_box_after_title($current_box);
			
		}
		
		public static function test_import() {
			echo("Imported \Admin\CustomPostTypes\ServerTransferCustomPostType<br />");
		}
	}
?>