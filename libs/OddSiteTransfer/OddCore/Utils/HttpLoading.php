<?php
	namespace OddSiteTransfer\OddCore\Utils;
	
	use \WP_Query;
	
	// \OddSiteTransfer\OddCore\Utils\HttpLoading
	class HttpLoading {
		
		public static function load($url) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			$data = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			/*
			echo($url);
			echo("\n");
			echo($httpcode);
			echo("\n");
			echo($data);
			echo("\n\n");
			*/
			
			return array('url' => $url, 'code' => $httpcode, 'data' => $data);
		}
		
		public static function send_request($url, $data) {
			
			$fields_string = http_build_query($data);
			//echo($fields_string."<br />");
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
			//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			$data = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			/*
			echo($url);
			echo("\n");
			echo($httpcode);
			echo("\n");
			echo($data);
			echo("\n\n");
			*/
			
			return array('url' => $url, 'code' => $httpcode, 'data' => $data);
		}
		
		public static function send_request_with_file($url, $data) {
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			$data = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			/*
			echo($url);
			echo("\n");
			echo($httpcode);
			echo("\n");
			echo($data);
			echo("\n\n");
			*/
			
			return array('url' => $url, 'code' => $httpcode, 'data' => $data);
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\OddCore\Utils\HttpLoading<br />");
		}
	}
?>