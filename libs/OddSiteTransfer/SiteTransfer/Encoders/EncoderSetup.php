<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	//use \WP_Post;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\EncoderSetup
	class EncoderSetup {
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\EncoderSetup::__construct<br />");
			
			
		}
		
		public static function create_post_encoder($post_types) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\EncoderSetup::create_post_encoder<br />");
			
			$qualifier = new \OddSiteTransfer\SiteTransfer\Qualifiers\PostTypeQualifier();
			$qualifier->add_post_types($post_types);
			
			$encoder = new \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder();
			$encoder->set_qualifier($qualifier);
			
			return $encoder;
		}
		
		public static function create_targeted_post_encoder($post_types, $targets = null, $condition = 'and') {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\EncoderSetup::create_targeted_post_encoder<br />");
			
			$qualifier = new \OddSiteTransfer\SiteTransfer\Qualifiers\PostTypeQualifier();
			$qualifier->add_post_types($post_types);
			
			$encoder = new \OddSiteTransfer\SiteTransfer\Encoders\TargetedSitePostEncoder();
			$encoder->set_qualifier($qualifier);
			
			if($targets) {
				foreach($targets as $target) {
					$encoder->add_term($target['term'], $target['taxonomy']);
				}
			}
			$encoder->set_condition($condition);
			
			return $encoder;
		}
		
		public static function add_meta_fields_to_encoder($encoder, $data_field_names = null, $object_field_names = null) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\EncoderSetup::add_meta_fields_to_encoder<br />");
			
			if($data_field_names) {
				foreach($data_field_names as $field_name) {
					$encoder->add_meta_field($field_name, 'data');
				}
			}
			
			if($object_field_names) {
				foreach($object_field_names as $field_name) {
					$encoder->add_meta_field($field_name, 'post_ids');
				}
			}
			
			return $encoder;
		}
		
		public static function create_attachment_post_encoder() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\EncoderSetup::create_attachment_post_encoder<br />");
			
			$post_types = array('attachment');
			
			$qualifier = new \OddSiteTransfer\SiteTransfer\Qualifiers\PostTypeQualifier();
			$qualifier->add_post_types($post_types);
			
			$encoder = new \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder();
			$encoder->set_qualifier($qualifier);
			
			//METODO: should caption be here?
			self::add_meta_fields_to_encoder($encoder, array('_wp_attached_file', '_wp_attachment_metadata', '_wp_attachment_image_alt'));
			
			return $encoder;
		}
		
		public static function create_term_encoder() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\EncoderSetup::create_term_encoder<br />");
			
			$all_quailifier = new \OddSiteTransfer\SiteTransfer\Qualifiers\AllQualifier();
			
			$encoder = new \OddSiteTransfer\SiteTransfer\Encoders\TermEncoderBaseObject();
			$encoder->set_qualifier($all_quailifier);
			
			return $encoder;
		}
		
		public static function create_user_encoder() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\EncoderSetup::create_user_encoder<br />");
			
			$all_quailifier = new \OddSiteTransfer\SiteTransfer\Qualifiers\AllQualifier();
			
			$encoder = new \OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject();
			$encoder->set_qualifier($all_quailifier);
			
			return $encoder;
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\EncoderSetup<br />");
		}
	}
?>