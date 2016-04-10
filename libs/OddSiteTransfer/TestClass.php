<?php
	namespace OddSiteTransfer;
	
	class TestClass {
		
		protected $_pages = null;
		
		function __construct() {
			echo("\OddSiteTransfer\TestClass::__construct<br />");
			
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\TestClass<br />");
		}
	}
?>