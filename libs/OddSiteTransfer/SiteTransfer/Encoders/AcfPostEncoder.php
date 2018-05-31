<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	use \WP_Post;
	use \OddSiteTransfer\SiteTransfer\Encoders\PostEncoderBaseObject;
	use \OddSiteTransfer\SiteTransfer\Encoders\AcfFieldEncoder;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder
	class AcfPostEncoder extends PostEncoderBaseObject {
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder::__construct<br />");
			
			parent::__construct();
		}
		
		protected function encode_meta_data($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder::encode_meta_data<br />");
			
			parent::encode_meta_data($object, $return_object);
			
			//METODO: move this as it's not efficient to have it here
			\OddSiteTransfer\OddCore\Utils\AcfFunctions::ensure_post_has_fields($object);
			
			$send_fields = array();
			
			$post_id = $object->ID;
			setup_postdata($object); 
			$acf_fields = get_field_objects($post_id, false, true);
			
			if($acf_fields) {
				foreach($acf_fields as $name => $acf_field) {
					$send_fields[$name] = AcfFieldEncoder::encode_acf_field($acf_field['value'], $acf_field, $post_id, $return_object['dependencies']);
				}
			}
			wp_reset_postdata();
			
			$return_object['meta_data']['acf'] = $send_fields;
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder<br />");
		}
	}
?>