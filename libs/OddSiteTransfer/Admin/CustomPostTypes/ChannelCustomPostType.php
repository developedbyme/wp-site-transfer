<?php
	namespace OddSiteTransfer\Admin\CustomPostTypes;
	
	use \OddSiteTransfer\OddCore\Admin\CustomPostTypes\CustomPostTypePost;
	
	// \OddSiteTransfer\Admin\CustomPostTypes\ChannelCustomPostType
	class ChannelCustomPostType extends CustomPostTypePost {
		
		function __construct() {
			//echo("\Admin\CustomPostTypes\ChannelCustomPostType::__construct<br />");
			
			parent::__construct();
			
			$this->set_names('ost_channel', 'channel');
			$this->_arguments['supports'] = array('title');
			$this->_arguments['taxonomies'] = array();
			$this->set_argument('public', false);
			$this->set_argument('publicly_queryable', false);
			$this->set_argument('exclude_from_search', true);
			$this->set_argument('has_archive', false);
			
			$current_box = new \OddSiteTransfer\OddCore\Admin\MetaData\PostMetaDataFieldBox();
			$current_box->set_name('URL');
			$current_box->set_meta_key('url');
			$this->add_meta_box_after_title($current_box);
			
			$current_box = new \OddSiteTransfer\OddCore\Admin\MetaData\PostMetaDataBox();
			$current_box->set_name('Settings');
			$current_box->set_nonce_name('settings_nonce');
			
			$select_field = new \OddSiteTransfer\OddCore\Admin\MetaData\SelectMetaField();
			$select_field->set_name('settings_name');
			$select_field->set_default_value('outgoing');
			$select_field->add_option('Outgoing', 'outgoing');
			$select_field->add_option('Incoming', 'incoming');
			$select_field->add_option('API', 'api');
			$current_box->add_meta_field($select_field);
			
			$this->add_meta_box_after_title($current_box);
			
		}
		
		public static function test_import() {
			echo("Imported \Admin\CustomPostTypes\ChannelCustomPostType<br />");
		}
	}
?>