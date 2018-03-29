<?php
	
	function ost_create_id() {
		$id = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
		
		return $id;
	}
	
	function ost_get_object_transfer_id($type, $object) {
		$id = get_metadata($type, $object, 'ost_transfer_id', true);
		var_dump($type, $id);
		if(!$id) {
			$id = ost_create_id();
			update_metadata($type, $object, 'ost_transfer_id', $id);
		}
		return $id;
	}
	
	function ost_get_post_transfer_id($post) {
		return ost_get_object_transfer_id('post', $post->ID);
	}
	
	function ost_get_user_transfer_id($user) {
		return ost_get_object_transfer_id('user', $user->ID);
	}
	
	function ost_get_post_id_for_transfer($transfer_id) {
		$args = array(
			'post_type' => 'any',
			'post_status' => 'any',
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => 'ost_transfer_id',
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
		
		//METODO: check if transfer alread exists
		
		$existing_id = ost_get_transfer_post_id($transfer_id);
		if($existing_id !== -1) {
			return $existing_id;
		}
		
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
			
			if($type === 'update') {
				update_post_meta($transfer_post_id, 'ost_transfer_status', 0);
			}
			else if($type === 'incoming') {
				update_post_meta($transfer_post_id, 'ost_transfer_status', 1);
				update_post_meta($transfer_post_id, 'ost_import_status', 0);
			}
			else {
				//METODO: error message
			}
		}
	}
	
	function ost_import_transfer($transfer_post_id) {
		
		$import_status = (int)get_post_meta($transfer_post_id, 'ost_import_status', true);
		
		$current_hash = get_post_meta($transfer_post_id, 'ost_encoded_data_hash', true);
		$imported_hash = get_post_meta($transfer_post_id, 'ost_imported_hash', true);
		
		if($imported_hash !== $current_hash || true) { //MEDEBUG: always true
			//METODO: check depndencies
			//METODO: do import
			$transfer_id = get_post_meta($transfer_post_id, 'ost_id', true);
			$data = get_post_meta($transfer_post_id, 'ost_encoded_data', true);
			$transfer_type = get_post_meta($transfer_post_id, 'ost_transfer_type', true);
			
			do_action(ODD_SITE_TRANSFER_DOMAIN.'/import_'.$transfer_type, $transfer_id, $data);
			
			update_post_meta($transfer_post_id, 'ost_imported_hash', $current_hash);
		}
	}
	
	function ost_get_post_dependency_for_transfer($transfer_id) {
		
		$post_id = ost_get_post_id_for_transfer($transfer_id);
		$post = get_post($post_id);
		
		$transfer_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/post_transfer_type', null, $post->ID, $post);
		
		if($transfer_type) {
			$transfer_post_id = ost_add_post_transfer($transfer_id, $transfer_type, $post);
		
			if($transfer_post_id !== -1) {
				ost_update_post_transfer($transfer_post_id, $post);
			}
		}
		else {
			//METODO: Add no-transfers
		}
		
		return $transfer_post_id;
	}
	
	function ost_get_user_dependency_for_transfer($transfer_id) {
		
		
		return $transfer_id;
	}
	
	function ost_get_dependency_for_transfer($transfer_id, $object_type) {
		$transfer_post_id = ost_get_transfer_post_id($transfer_id);
		
		if($transfer_post_id !== -1) {
			return $transfer_post_id;
		}
		
		switch($object_type) {
			case 'post':
				return ost_get_post_dependency_for_transfer($transfer_id);
			case 'user':
				return ost_get_user_dependency_for_transfer($transfer_id);
			default:
				trigger_error('Unknown dependency type '.$object_type, E_USER_WARNING);
				break;
		}
		
		return -1;
	}
	
	function ost_get_dependency($transfer_id, $type) {
		$transfer_post_id = ost_get_transfer_post_id($transfer_id);
		
		if($transfer_post_id === -1) {
			return null;
		}
		
		//METODO: run import if not imported yet
		
		$transfer_type = get_post_meta($transfer_post_id, 'ost_transfer_type', true);
		
		if($transfer_type === 'post') {
			return get_post(ost_get_post_id_for_transfer($transfer_id));
		}
		if($transfer_type === 'media') {
			return get_post(ost_get_post_id_for_transfer($transfer_id));
		}
		
		return null;
	}
?>