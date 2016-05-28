<?php
	namespace OddSiteTransfer\SiteTransfer\Qualifiers;
	
	//use \WP_Query;
	
	// \OddSiteTransfer\SiteTransfer\Qualifiers\QualifierBaseObject
	class QualifierBaseObject {
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Qualifiers\QualifierBaseObject::__construct<br />");
			
			
		}
		
		public function qualify($object) {
			//echo("\OddSiteTransfer\SiteTransfer\Qualifiers\QualifierBaseObject::qualify<br />");
			
			//MENOTE: should be overridden
			
			return false;
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Qualifiers\QualifierBaseObject<br />");
		}
	}
?>