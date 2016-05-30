<?php
	namespace OddSiteTransfer\SiteTransfer;
	
	//use \WP_Query;
	
	use \OddSiteTransfer\OddCore\Utils\HttpLoading as HttpLoading;
	
	// \OddSiteTransfer\SiteTransfer\ServerSettings
	class ServerSettings {
		
		protected $post_encoders = array();
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::__construct<br />");
			
			
		}
		
		public function add_encoder($encoder) {
			$this->post_encoders[] = $encoder;
			
			return $this;
		}
		
		public function qualify_post($post) {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::qualify_post<br />");
			
			foreach($this->post_encoders as $encoder) {
				if($encoder->qualify($post)) {
					return true;
				}
			}
			
			return false;
		}
		
		public function transfer_post($post, $server_transfer_post) {
			//echo("\OddSiteTransfer\SiteTransfer\ServerSettings::transfer_post<br />");
			
			$server_transfer_post_id = $server_transfer_post->ID;
			
			$base_url = get_post_meta($server_transfer_post_id, 'url', true);
			$url = $base_url.'sync/post';
			
			foreach($this->post_encoders as $encoder) {
				if($encoder->qualify($post)) {
					//METODO
					$encoded_data = $encoder->encode($post);
					//var_dump($encoded_data);
					
					$result_data = HttpLoading::send_request($url, $encoded_data);
					var_dump($result_data);
					//var_dump($result_data['data']);
					$result_object = json_decode($result_data['data']);
					//var_dump($result_object);
					
					if($result_object->code === 'success') {
						$missing_dependencies = $result_object->data->missingDependencies;
						var_dump($missing_dependencies);
					}
				}
			}
			
			return false; //MEDEBUG
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\ServerSettings<br />");
		}
	}
?>