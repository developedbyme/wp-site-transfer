<?php
	
	function ost_create_id() {
		$id = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
		
		return $id;
	}
	
	function ost_get_object_transfer_id($type, $object) {
		$id = get_metadata($type, $object, 'ost_transfer_id', true);
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
	
	function ost_get_term_transfer_id($term) {
		return ost_get_object_transfer_id('term', $term->term_id);
	}
	
	function ost_get_post_id_for_transfer($transfer_id) {
		
		$post_statuses = array("any", "draft", "pending", "private", "publish", "wc-pending", "wc-customer-loss", "wc-investigate", "wc-produced", "wc-on-hold", "wc-completed", "wc-refunded", "wc-cancelled", "wc-failed", "wc-goodwill", "wc-processing", "wc-kco-incomplete");
		
		remove_all_actions('pre_get_posts');
		
		$post_types = get_post_types(array(), 'names');
		
		$args = array(
			'post_type' => $post_types,
			'post_status' => $post_statuses,
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
	
	function ost_get_user_id_for_transfer($transfer_id) {
		$args = array(
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => 'ost_transfer_id',
					'value' => $transfer_id,
					'compare' => '='
				)
			)

		); 
		$users = get_users( $args );
		
		if(empty($users)) {
			return -1;
		}
		
		return $users[0];
	}
	
	function ost_get_term_for_transfer($transfer_id) {
		$args = array(
			'fields' => 'ids',
			'hide_empty' => false,
			'meta_query' => array(
				array(
					'key' => 'ost_transfer_id',
					'value' => $transfer_id,
					'compare' => '='
				)
			)
		); 
		
		
		$taxonomies = get_taxonomies();
		foreach($taxonomies as $taxonomy) {
			$terms = get_terms($taxonomy, $args);
			if(!empty($terms)) {
				return get_term_by('id', $terms[0], $taxonomy);
			}
		}
		
		return null;
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
		
		return (int)$posts[0];
	}
	
	function ost_create_transfer($transfer_id, $transfer_type, $name) {
		$existing_id = ost_get_transfer_post_id($transfer_id);
		if($existing_id !== -1) {
			return $existing_id;
		}
		
		$args = array(
			'post_type' => 'ost_transfer',
			'post_status' => 'publish',
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
		
		$transfer_post_id = ost_create_transfer($transfer_id, $transfer_type, $transfer_type.' - '.($post->post_title).' - '.$transfer_id);
	
		if(!$transfer_post_id) {
			return -1;
		}
		
		ost_update_post_transfer($transfer_post_id, $post);
		
		return $transfer_post_id;
	}
	
	function ost_update_post_transfer($transfer_post_id, $post) {
		
		$encoder = new \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder();
		
		$transfer_id = get_post_meta($transfer_post_id, 'ost_id', true);
		$transfer_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/post_transfer_type', null, $post->ID, $post);
		$transfer_title = $transfer_type.' - '.($post->post_title).' - '.$transfer_id;
		
		$encoder = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/encoder_setup/post', $encoder, $transfer_type, $post->ID, $post);
		
		$encoded_data = $encoder->encode($post, $transfer_type);
		ost_update_transfer($transfer_post_id, $encoded_data, $transfer_type, $transfer_title);
		
		$encoded_meta_data = array();
		$meta_data = get_post_meta($post->ID);
		foreach($meta_data as $key => $value) {
			$encoded_meta_data[$key] = get_post_meta($post->ID, $key, false);
		}
		update_post_meta($transfer_post_id, 'ost_raw_meta', $encoded_meta_data);
	}
	
	function ost_add_user_transfer($transfer_id, $transfer_type, $user) {
		
		$transfer_post_id = ost_create_transfer($transfer_id, $transfer_type, $transfer_type.' - '.($user->user_login).' - '.$transfer_id);
		
		ost_update_user_transfer($transfer_post_id, $user);
		
		return $transfer_post_id;
	}
	
	function ost_update_user_transfer($transfer_post_id, $user) {
		$encoder = new \OddSiteTransfer\SiteTransfer\Encoders\UserEncoderBaseObject();
		
		$transfer_id = get_post_meta($transfer_post_id, 'ost_id', true);
		$transfer_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/user_transfer_type', null, $user->ID, $user);
		$transfer_title = $transfer_type.' - '.($user->user_login).' - '.$transfer_id;
		
		$encoded_data = $encoder->encode($user);
		ost_update_transfer($transfer_post_id, $encoded_data, $transfer_type, $transfer_title);
	}
	
	function ost_add_term_transfer($transfer_id, $transfer_type, $term) {
		$transfer_post_id = ost_create_transfer($transfer_id, $transfer_type, $transfer_type.' - '.($term->name).' - '.$transfer_id);
		
		ost_update_term_transfer($transfer_post_id, $term);
		
		return $transfer_post_id;
	}
	
	function ost_update_term_transfer($transfer_post_id, $term) {
		$encoder = new \OddSiteTransfer\SiteTransfer\Encoders\TermEncoderBaseObject();
		
		$transfer_id = get_post_meta($transfer_post_id, 'ost_id', true);
		$transfer_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/term_transfer_type', null, $term->term_id, $term);
		$transfer_title = $transfer_type.' - '.($term->name).' - '.$transfer_id;
		
		$encoded_data = $encoder->encode($term);
		ost_update_transfer($transfer_post_id, $encoded_data, $transfer_type, $transfer_title);
	}
	
	function ost_update_transfer($transfer_post_id, $encoded_data, $transfer_type, $title, $type = 'update') {
		$encoded_data_hash = md5(serialize($encoded_data));
		
		$current_hash = get_post_meta($transfer_post_id, 'ost_encoded_data_hash', true);
		if($title !== get_the_title($transfer_post_id)) {
			wp_update_post(array('ID' => $transfer_post_id, 'post_title' => $title));
		}
		update_post_meta($transfer_post_id, 'ost_transfer_type', $transfer_type);
		
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
			//METODO: check dependencies
			$transfer_id = get_post_meta($transfer_post_id, 'ost_id', true);
			$data = get_post_meta($transfer_post_id, 'ost_encoded_data', true);
			$transfer_type = get_post_meta($transfer_post_id, 'ost_transfer_type', true);
			
			do_action(ODD_SITE_TRANSFER_DOMAIN.'/import_'.$transfer_type, $transfer_id, $data);
			
			update_post_meta($transfer_post_id, 'ost_import_status', 1);
			update_post_meta($transfer_post_id, 'ost_imported_hash', $current_hash);
		}
	}
	
	function ost_get_post_dependency_for_transfer($transfer_id) {
		
		$post_id = ost_get_post_id_for_transfer($transfer_id);
		$post = get_post($post_id);
		
		$transfer_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/post_transfer_type', null, $post->ID, $post);
		
		$transfer_post_id = -1;
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
		
		$user_id = ost_get_user_id_for_transfer($transfer_id);
		$user = get_user_by('id', $user_id);
		
		$transfer_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/user_transfer_type', null, $user->ID, $user);
		
		$transfer_post_id = -1;
		if($transfer_type) {
			$transfer_post_id = ost_add_user_transfer($transfer_id, $transfer_type, $user);
		
			if($transfer_post_id !== -1) {
				ost_update_user_transfer($transfer_post_id, $user);
			}
		}
		
		return $transfer_post_id;
	}
	
	function ost_get_term_dependency_for_transfer($transfer_id) {
		
		$term = ost_get_term_for_transfer($transfer_id);
		$term_id = $term->term_id;
		
		$transfer_type = apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/term_transfer_type', null, $term_id, $term);
		
		$transfer_post_id = -1;
		if($transfer_type) {
			$transfer_post_id = ost_add_term_transfer($transfer_id, $transfer_type, $term);
		
			if($transfer_post_id !== -1) {
				ost_update_term_transfer($transfer_post_id, $term);
			}
		}
		
		return $transfer_post_id;
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
			case 'term':
				return ost_get_term_dependency_for_transfer($transfer_id);
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
		
		//METODO: move out these to be mor flexible
		if($transfer_type === 'post' || $transfer_type === 'legacy_order' || $transfer_type === 'legacy_subscription' || $transfer_type === 'legacy_quiz_response') {
			return get_post(ost_get_post_id_for_transfer($transfer_id));
		}
		if($transfer_type === 'media') {
			return get_post(ost_get_post_id_for_transfer($transfer_id));
		}
		if($transfer_type === 'user') {
			return get_user_by('id', ost_get_user_id_for_transfer($transfer_id));
		}
		if($transfer_type === 'term') {
			return ost_get_term_for_transfer($transfer_id);
		}
		
		return apply_filters(ODD_SITE_TRANSFER_DOMAIN.'/get_dependency/'.$transfer_type, null, $transfer_id, $transfer_type);
	}
?>