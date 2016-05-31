<?php
	namespace OddSiteTransfer\SiteTransfer\Qualifiers;
	
	use \OddSiteTransfer\SiteTransfer\Qualifiers\QualifierBaseObject;
	
	// \OddSiteTransfer\SiteTransfer\Qualifiers\AllQualifier
	class AllQualifier extends QualifierBaseObject {
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Qualifiers\AllQualifier::__construct<br />");
			
			
		}
		
		public function qualify($object) {
			//echo("\OddSiteTransfer\SiteTransfer\Qualifiers\AllQualifier::qualify<br />");
			
			return true;
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Qualifiers\AllQualifier<br />");
		}
	}
?>