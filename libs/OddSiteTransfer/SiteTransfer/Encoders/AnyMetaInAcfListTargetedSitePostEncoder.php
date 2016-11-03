<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	use \WP_Post;
	use \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\AnyMetaInAcfListTargetedSitePostEncoder
	class AnyMetaInAcfListTargetedSitePostEncoder extends AcfPostEncoder {
		
		protected $match_type = 'and';
		protected $matches = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\AnyMetaInAcfListTargetedSitePostEncoder::__construct<br />");
			
			
		}
		
		public function set_condition($condition) {
			$this->match_type = $condition;
			
			return $this;
		}
		
		public function add_meta($list, $key, $value) {
			$this->matches[] = array('list' => $list, 'key' => $key, 'value' => $value);
		}
		
		protected function encode_status($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\AnyMetaInAcfListTargetedSitePostEncoder::encode_status<br />");
			
			//METODO: move this as it's not efficient to have it here
			\OddSiteTransfer\OddCore\Utils\AcfFunctions::ensure_post_has_fields($object);
			
			$post_id = $object->ID;
			
			//METODO: break out so that and and or can be grouped
			
			$should_exist = $this->match_type === 'and' ? true : false;
			foreach($this->matches as $match_data) {
				
				$has_match = false;
				
				if(have_rows($match_data['list'], $post_id)) {
					
					$rows = get_field($match_data['list'], $post_id);
					
					foreach($rows as $row) {
						if($row[$match_data['key']] === $match_data['value']) {
							$has_match = true;
							break;
						}
					}
				}
				
				if($has_match) {
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
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\AnyMetaInAcfListTargetedSitePostEncoder<br />");
		}
	}
?>