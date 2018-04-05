<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	use \WP_Post;
	use \OddSiteTransfer\SiteTransfer\Encoders\WcOrderPostEncoder;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\WcSubscriptionPostEncoder
	class WcSubscriptionPostEncoder extends WcOrderPostEncoder {
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\WcSubscriptionPostEncoder::__construct<br />");
			
			parent::__construct();
		}
		
		protected function encode_meta_data($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\WcSubscriptionPostEncoder::encode_meta_data<br />");
			
			parent::encode_meta_data($object, $return_object);
			
			//METODO: endode subscription data
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\WcSubscriptionPostEncoder<br />");
		}
	}
?>