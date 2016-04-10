<?php
	namespace OddSiteTransfer\Admin\CustomPostTypes;
	
	use \OddSiteTransfer\OddCore\Admin\CustomPostTypes\CustomPostTypePost;
	
	// \OddSiteTransfer\Admin\CustomPostTypes\ServerTransferCustomPostType
	class ServerTransferCustomPostType extends CustomPostTypePost {
		
		function __construct() {
			//echo("\Admin\CustomPostTypes\ServerTransferCustomPostType::__construct<br />");
			
			parent::__construct();
			
			$this->set_names("server-transfer", 'server transfer');
			$this->_arguments['supports'] = array('title');
			
			//$this->add_css('wallpaper-ad', plugins_url('assets/css/admin/wallpaper-ad.css', BTDM_PARALLAX_ADS_MAIN_FILE));
			
			//$this->add_javascript('react-with-addons', plugins_url('assets/js/libs/react-with-addons.js', BTDM_PARALLAX_ADS_MAIN_FILE));
			//$this->add_javascript('react-dom', plugins_url('assets/js/libs/react-dom.js', BTDM_PARALLAX_ADS_MAIN_FILE));
			//$this->add_javascript('wallpaper-ad', plugins_url('assets/js/admin/wallpaper-ad.js', BTDM_PARALLAX_ADS_MAIN_FILE));
			
			$link_box = new \OddSiteTransfer\OddCore\Admin\MetaData\PostMetaDataFieldBox();
			$link_box->set_name('URL');
			$link_box->set_meta_key('url');
			$this->add_meta_box_after_title($link_box);
			
		}
		
		public static function test_import() {
			echo("Imported \Admin\CustomPostTypes\ServerTransferCustomPostType<br />");
		}
	}
?>