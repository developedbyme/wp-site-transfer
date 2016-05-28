<?php
	namespace OddSiteTransfer\SiteTransfer\Qualifiers;
	
	use \OddSiteTransfer\SiteTransfer\Qualifiers\QualifierBaseObject;
	
	// \OddSiteTransfer\SiteTransfer\Qualifiers\PostTypeQualifier
	class PostTypeQualifier extends QualifierBaseObject {
		
		protected $post_types = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Qualifiers\PostTypeQualifier::__construct<br />");
			
			
		}
		
		public function add_post_types($post_types) {
			$this->post_types = array_merge($this->post_types, $post_types);
			
			return $this;
		}
		
		public function qualify($object) {
			//echo("\OddSiteTransfer\SiteTransfer\Qualifiers\PostTypeQualifier::qualify<br />");
			
			foreach($this->post_types as $post_type) {
				if($object->post_type === $post_type) {
					return true;
				}
			}
			
			return false;
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Qualifiers\PostTypeQualifier<br />");
		}
	}
?>