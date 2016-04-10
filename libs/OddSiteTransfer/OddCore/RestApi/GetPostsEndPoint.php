<?php
	namespace OddSiteTransfer\OddCore\RestApi;
	
	use \WP_Query;
	use OddSiteTransfer\OddCore\RestApi\EndPoint as EndPoint;
	
	// \OddSiteTransfer\OddCore\RestApi\GetPostsEndPoint
	class GetPostsEndPoint extends EndPoint {
		
		protected $_arguments = array();
		
		function __construct() {
			//echo("\OddCore\RestApi\GetPostsEndPoint::__construct<br />");
			
			$this->set_arguments(array("post_type" => "post", "post_status" => "publish"));
		}
		
		public function set_arguments($arguments) {
			
			foreach($arguments as $key => $value) {
				$this->_arguments[$key] = $value;
			}
			
			return $this;
		}
		
		protected function get_image_tag($media_id) {
			
			$return_string = '';
			
			if($media_id) {
				$thumb_photographer = get_post_meta($media_id, '_photographer', true);
				$thumb_title = get_post_field('post_excerpt', $media_id);
				
				$return_string .= '<div class="main-img figure"><div class="figure-img">';
				$return_string .= wp_get_attachment_image($media_id, 'large');
				$return_string .= '</div>';
				
				if($thumb_title || $thumb_photographer) {
					$return_string .= '<div class="figcaption">';
					if($thumb_photographer) {
						$return_string .= '<div class="credits"><span class="fn">'.$thumb_photographer.'</span></div>';
					}
					if($thumb_title) {
						$return_string .= apply_filters('the_excerpt', $thumb_title);
					}
					$return_string .= '</div>';
				}
				$return_string .= '</div>';
			}
			
			return $return_string;
		}
		
		protected function get_image_gallery_ids($rules) {
			
			$includes = array();
			$excludes = array();
		
			foreach ( $rules AS $rule ) {
				if ( !is_array($rule[1]) ) {
					$rule[1] = array( $rule[1] );
				}
				if ( $rule[0] == "include" ) {
					$includes = array_merge( $includes, $rule[1] );
				}
				elseIf ( $rule[0] == "exclude" ) {
					$excludes = array_merge( $excludes, $rule[1] );
				}
			}
		
			$tax_query = array();
			if ( !empty($includes) ) {
				$tax_query[] = array(
					'taxonomy' => "image_tag",
					'field' => 'id',
					'terms' => $includes,
					'include_children' => false,
					'operator' => 'IN'
				);
			}
			if ( !empty($excludes) ) {
				$tax_query[] = array(
					'taxonomy' => "image_tag",
					'field' => 'id',
					'terms' => $excludes,
					'include_children' => false,
					'operator' => 'NOT IN'
				);
			}
		
			if ( empty($includes) && empty($excludes) ) {
				return false;
			}
		
			if ( !empty($includes) && !empty($excludes) ) {
				$tax_query['relation'] = "AND";
			}
			
			$gallery_query = new \WP_Query( array(
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'posts_per_page' => -1,
				'tax_query' => $tax_query,
			) );
			
			$return_array = array();
			
			if ( is_object($gallery_query) && $gallery_query->have_posts() ) {
				while ( $gallery_query->have_posts() ) {
					$gallery_query->the_post();
					
					$current_data = array();
					
					$return_array[] = get_the_ID();
				}
			}
		
			return $return_array;
		}
		
		public function perform_call($data) {
			//echo("\OddCore\RestApi\GetPostsEndPoint::perform_call<br />");
			
			$post_type = $data["postType"];
			$posts_per_page = intval($data["postsPerPage"]);
			$offset = intval($data["offset"]);
			
			$return_array = array();
			
			$this->set_arguments(array(
				"post_type" => $post_type, 
				"posts_per_page" => $posts_per_page,
				"offset" => $offset
			));
			$query = new WP_Query($this->_arguments);
			
			while ( $query->have_posts() ) {
				
				$query->the_post();
				
				$post = get_post();
				$id = get_the_id();
				$thumbnail_id = get_post_thumbnail_id($id);
				
				$thumbnail_source = NULL;
				if($thumbnail_id) {
					$thumbnail_data = wp_get_attachment_image_src($thumbnail_id, 'single-post-thumbnail');
					$thumbnail_source = $thumbnail_data[0];
				}
				
				$taxonomies = array_keys(get_the_taxonomies($id));
				$term_data_array = array();
				foreach($taxonomies as $taxonomy) {
					$term_data_array[$taxonomy] = get_the_terms($id, $taxonomy);
				}
				
				$rich_content_boxes = array();
				$top_boxes_data = array();
				$bottom_boxes_data = array();
				
				$top_boxes = get_post_meta($id, '_rich_content', true);
				if(is_array($top_boxes)) {
					
					foreach($top_boxes AS $mb => $display) {
						if($display) {
							$meta = null;
							$content = null;
							if($mb === 'htmlcss') {
								$meta = get_post_meta($id, '_richcontent_html', true);
								$content = $meta;
							}
							elseif($mb === 'image') {
								$meta = get_post_thumbnail_id();
								$content = $this->get_image_tag($meta);
							}
							elseif($mb === 'imggallery') {
								$meta = get_post_meta($id, '_richcontent_imggallery', true);
								$content = "[gallery size=\"wrapper_width\" columns=\"1\" ids=\"".implode(',', $this->get_image_gallery_ids($meta))."\"]";
							}
							elseif($mb === 'pdfsales') {
								$meta = get_post_meta($id, '_pdfsale_file_id', true);
								$content = "[pdfsale id=\"{$meta}\"]";
							}
							elseif($mb === 'quizpoll') {
								//MENOTE: poll doesn't have any meta
								$content = "[poll id=\"{$id}\"]";
							}
							elseif($mb === 'video') {
								$meta = get_post_meta($id, '_richcontent_video', true);
								$content = $meta;
								//MENOTE: data is stored as url and not the oembed
								/*
								if(filter_var($meta, FILTER_VALIDATE_URL)) {
									$content = wp_oembed_get($meta, array('width' => 700));
								}
								*/
							}
							array_push($top_boxes_data, array('type' => $mb, 'content' => $content, 'meta' => $meta));
						}
					}
				}
				$bottom_boxes = get_post_meta($id, '_rich_content_bottom', true);
				if(is_array($bottom_boxes)) {
					
					foreach($bottom_boxes AS $mb => $display) {
						if($display) {
							$meta = null;
							$content = null;
							if($mb === 'htmlcss') {
								$meta = get_post_meta($id, '_richcontent_html_bottom', true);
								$content = $meta;
							}
							elseif($mb === 'image') {
								$meta = get_post_meta(get_the_ID(), '_richcontent_image_bottom', true);
								$content = $this->get_image_tag($meta);
							}
							elseif($mb === 'imggallery') {
								$meta = get_post_meta($id, '_richcontent_imggallery_bottom', true);
								$content = "[gallery size=\"wrapper_width\" columns=\"1\" ids=\"".implode(',', $this->get_image_gallery_ids($meta))."\"]";
							}
							elseif($mb === 'pdfsales') {
								$meta = get_post_meta($id, '_pdfsale_file_id_bottom', true);
								$content = "[pdfsale id=\"{$meta}\"]";
							}
							elseif($mb === 'quizpoll') {
								//MENOTE: poll doesn't have any meta
								$content = "[poll id=\"{$id}\"]";
							}
							elseif($mb === 'video') {
								$meta = get_post_meta($id, '_richcontent_video_bottom', true);
								$content = $meta;
								//MENOTE: data is stored as url and not the oembed
								/*
								if(filter_var($meta, FILTER_VALIDATE_URL)) {
									$content = wp_oembed_get($meta, array('width' => 700));
								}
								*/
							}
							array_push($bottom_boxes_data, array('type' => $mb, 'content' => $content, 'meta' => $meta));
						}
					}
				}
				
				$rich_content_boxes['top'] = $top_boxes_data;
				$rich_content_boxes['bottom'] = $bottom_boxes_data;
				
				$return_array[] = array(
					"id" => $id,
					"title" => get_the_title(),
					"permalink" => get_permalink(),
					"thumbnailId" => $thumbnail_id,
					"thumbnailSource" => $thumbnail_source,
					"content" => $post->post_content,
					"terms" => $term_data_array,
					"richContentBoxes" => $rich_content_boxes
				);
			}
			wp_reset_postdata();
			
			return $this->output_success($return_array);
		}
		
		public static function test_import() {
			echo("Imported \OddCore\RestApi\GetPostsEndPoint<br />");
		}
	}
?>