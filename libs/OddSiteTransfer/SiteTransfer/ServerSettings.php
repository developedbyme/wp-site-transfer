<?php
	namespace OddSiteTransfer\SiteTransfer;
	
	//use \WP_Query;
	
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
			echo("\OddSiteTransfer\SiteTransfer\ServerSettings::transfer_post<br />");
			
			foreach($this->post_encoders as $encoder) {
				if($encoder->qualify($post)) {
					//METODO
					$encoded_data = $encoder->encode($post);
					var_dump($encoded_data);
				}
			}
			
			return false; //MEDEBUG
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\ServerSettings<br />");
		}
	}
?>