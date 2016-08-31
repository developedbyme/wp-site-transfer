<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	use \WP_Post;
	use \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\MetaTargetedSitePostEncoder
	class MetaTargetedSitePostEncoder extends AcfPostEncoder {
		
		protected $match_type = 'and';
		protected $matches = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\MetaTargetedSitePostEncoder::__construct<br />");
			
			
		}
		
		public function set_condition($condition) {
			$this->match_type = $condition;
			
			return $this;
		}
		
		public function add_meta($key, $value) {
			$this->matches[] = array('key' => $key, 'value' => $value);
		}
		
		protected function encode_status($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\MetaTargetedSitePostEncoder::encode_status<br />");
			
			//METODO: break out so that and and or can be grouped
			
			$should_exist = $this->match_type === 'and' ? true : false;
			foreach($this->matches as $match_data) {
				if(get_post_meta($object->ID, $match_data['key'], true) === $match_data['value']) {
					if($this->match_type === 'or') {
						$should_exist = true;
						break;
					}
				}
				else {
					if($this->match_type === 'and') {
						$should_exist = false;
						break;
					}
				}
			}
			
			if($should_exist) {
				parent::encode_status($object, $return_object);
			}
			else {
				$return_object['status'] = 'non-existing';
			}
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\MetaTargetedSitePostEncoder<br />");
		}
	}
?>