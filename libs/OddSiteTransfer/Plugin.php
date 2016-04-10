<?php
	namespace OddSiteTransfer;
	
	use \OddSiteTransfer\OddCore\PluginBase;
	
	class Plugin extends PluginBase {
		
		function __construct() {
			//echo("\OddSiteTransfer\Plugin::__construct<br />");
			
			parent::__construct();
		}
		
		protected function create_pages() {
			//echo("\OddSiteTransfer\Plugin::create_pages<br />");
			
		}
		
		protected function create_additional_hooks() {
			//echo("\OddSiteTransfer\Plugin::create_additional_hooks<br />");
			
			$this->add_additional_hook(new \OddSiteTransfer\Admin\TransferHooks());
		}
		
		protected function create_rest_api_end_points() {
			//echo("\OddSiteTransfer\Plugin::create_rest_api_end_points<br />");
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\Plugin<br />");
		}
	}
?>