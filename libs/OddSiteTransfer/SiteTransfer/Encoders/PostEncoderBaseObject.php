<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	//use \WP_Query;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject
	class PostEncoderBaseObject {
		
		protected $qualifier = null;
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::__construct<br />");
			
			
		}
		
		public function set_qualifier($qualifier) {
			$this->qualifier = $qualifier;
			
			return $this;
		}
		
		public function qualify($object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject::qualify<br />");
			
			return $this->qualifier->qualify($object);
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject<br />");
		}
	}
?>