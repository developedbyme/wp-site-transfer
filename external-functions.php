<?php
	
	function ost_get_post_transfer_id($post) {
		$id = get_post_meta($post->ID, 'ost_transfer_id', true);
		if(!$id) {
			$id = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
			update_post_meta($post->ID, 'ost_transfer_id', $id);
		}
		
		return $id;
	}
	
	function ost_get_transfer_post_id($transfer_id) {
		$args = array(
			'post_type' => 'ost_transfer',
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => 'ost_id',
					'value' => $transfer_id,
					'compare' => '='
				)
			)
		);
		
		$posts = get_posts($args);
		
		if(empty($posts)) {
			return -1;
		}
		
		return $posts[0];
	}
	
	function ost_create_transfer($transfer_id, $transfer_type, $name) {
		$args = array(
			'post_type' => 'ost_transfer',
			'post_status' => 'draft',
			'post_title' => $name
		);
		
		$transfer_post_id = wp_insert_post($args);
		
		if($transfer_post_id) {
			update_post_meta($transfer_post_id, 'ost_id', $transfer_id);
			update_post_meta($transfer_post_id, 'ost_transfer_type', $transfer_type);
		}
		
		return $transfer_post_id;
	}
	
	function ost_add_post_transfer($transfer_id, $transfer_type, $post) {
		
		$publish_ids = array();
		
		$transfer_post_id = ost_create_transfer($transfer_id, $transfer_type, $transfer_type.' - '.($post->post_title));
	
		if(!$transfer_post_id) {
			return -1;
		}
		
		$publish_ids[] = $transfer_post_id;
		
		ost_update_post_transfer($transfer_post_id, $post);
		
		foreach($publish_ids as $publish_id) {
			wp_update_post(array('ID' => $publish_id, 'post_status' => 'publish'));
		}
		
		return $transfer_post_id;
	}
	
	function ost_update_post_transfer($transfer_post_id, $post) {
		
		$encoder = new \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder();
		
		$encoded_data = $encoder->encode($post);
		ost_update_transfer($transfer_post_id, $encoded_data);
	}
	
	function ost_update_transfer($transfer_post_id, $encoded_data, $type = 'update') {
		$encoded_data_hash = md5(serialize($encoded_data));
		
		$current_hash = get_post_meta($transfer_post_id, 'ost_encoded_data_hash', true);
		
		if($encoded_data_hash !== $current_hash) {
			update_post_meta($transfer_post_id, 'ost_encoded_data', $encoded_data);
			update_post_meta($transfer_post_id, 'ost_encoded_data_hash', $encoded_data_hash);
			
			update_post_meta($transfer_post_id, 'ost_transfer_status', 0);
		}
	}
	
	function ost_import_transfer($transfer_post_id) {
		$current_hash = get_post_meta($transfer_post_id, 'ost_encoded_data_hash', true);
		$imported_hash = get_post_meta($transfer_post_id, 'ost_imported_hash', true);
		
		if($imported_hash !== $current_hash || true) { //MEDEBUG: always true
			//METODO: do import
			update_post_meta($transfer_post_id, 'ost_imported_hash', $current_hash);
		}
	}
?>