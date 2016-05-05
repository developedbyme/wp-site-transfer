<?php
	namespace OddSiteTransfer\OddCore\Admin\CustomPostTypes;
	
	// \OddSiteTransfer\OddCore\Admin\CustomPostTypes\CustomPostTypePost
	class CustomPostTypePost {
		
		//METODO: create base object for posts and pages
		
		protected $_system_name = null;
		
		protected $_labels = array(
			'name' => 'Custom post types',
			'singular_name' => 'Custom post type',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Custom post type',
			'edit_item' => 'Edit Custom post type',
			'new_item' => 'New Custom post type',
			'all_items' => 'All Custom post types',
			'view_item' => 'View Custom post type',
			'search_items' => 'Search Custom post types',
			'not_found' => 'No custom post types found',
			'not_found_in_trash' => 'No custom post types found in trash',
			'parent_item_colon' => '',
			'menu_name' => 'Custom post types'
		);
		protected $_arguments = array();
		
		protected $_meta_boxes_after_title = array();
		protected $_meta_boxes_after_editor = array();
		
		public $javascript_files = array();
		public $javascript_data = array();
		public $css_files = array();
		
		function __construct() {
			//echo("\OddCore\Admin\CustomPostTypes\CustomPostTypePost::__construct<br />");
			
			$this->_arguments = array(
				'description' => null,
				'public' => false,
				'exclude_from_search' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_in_menu' => true,
				'show_in_admin_bar' => true,
				'menu_position' => null,
				'menu_icon' => null,
				'capability_type' => 'post',
				//'capabilities' => array(),
				'hierarchical' => false,
				'supports' => array( 'title', 'editor', 'author', 'comments', 'thumbnail', 'revisions' ),
				'taxonomies' => array(),
				'has_archive' => true,
				'rewrite' => array( 'slug' => $this->_system_name ),
				'query_var' => true
			);
		}
		
		public function get_system_name() {
			return $this->_system_name;
		}
		
		public function set_names($system_name, $dipslay_name = null) {
			//echo("\OddCore\Admin\CustomPostTypes\CustomPostTypePost::set_names<br />");
			
			$this->_system_name = $system_name;
			
			if(isset($dipslay_name)) {
				$this->setup_labels_autonaming($dipslay_name);
			}
			
			return $this;
		}
		
		public function setup_labels_autonaming($name) {
			
			$multiple_name = $name.'s'; //METODO: do better
			//METODO: fix parent_item_colon
			
			$this->_labels = array(
				'name' => __( ucfirst($multiple_name), ODD_SITE_TRANSFER_TEXTDOMAIN ),
				'singular_name' => __( ucfirst($name), ODD_SITE_TRANSFER_TEXTDOMAIN ),
				'add_new' => __( 'Add New', ODD_SITE_TRANSFER_TEXTDOMAIN ),
				'add_new_item' => __( 'Add New '.ucfirst($name), ODD_SITE_TRANSFER_TEXTDOMAIN ),
				'edit_item' => __( 'Edit '.ucfirst($name), ODD_SITE_TRANSFER_TEXTDOMAIN ),
				'new_item' => __( 'New '.ucfirst($name), ODD_SITE_TRANSFER_TEXTDOMAIN ),
				'all_items' => __( 'All '.ucfirst($multiple_name), ODD_SITE_TRANSFER_TEXTDOMAIN ),
				'view_item' => __( 'View '.ucfirst($name), ODD_SITE_TRANSFER_TEXTDOMAIN ),
				'search_items' => __( 'Search '.ucfirst($multiple_name), ODD_SITE_TRANSFER_TEXTDOMAIN ),
				'not_found' => __( 'No '.$name.' found', ODD_SITE_TRANSFER_TEXTDOMAIN ),
				'not_found_in_trash' => __( 'No '.$name.' found in trash', ODD_SITE_TRANSFER_TEXTDOMAIN ),
				'parent_item_colon' => '',
				'menu_name' => __( ucfirst($multiple_name), ODD_SITE_TRANSFER_TEXTDOMAIN )
			);
		}
		
		public function add_meta_box_after_title($meta_box) {
			$this->_meta_boxes_after_title[] = $meta_box;
			
			return $this;
		}
		
		public function add_meta_box_after_editor($meta_box) {
			$this->_meta_boxes_after_editor[] = $meta_box;
			
			return $this;
		}
		
		public function register() {
			//echo("\OddCore\Admin\CustomPostTypes\CustomPostTypePost::register<br />");
			
			$this->_arguments['labels'] = $this->_labels;
			
			register_post_type($this->_system_name, $this->_arguments);
		}
		
		public function enqueue_scripts_and_styles() {
			//echo("\OddCore\Admin\Pages\Page::enqueue_scripts_and_styles<br />");
			
			foreach($this->javascript_files as $id => $path) {
				wp_enqueue_script($id, $path);
			}
			
			foreach($this->javascript_data as $file_id => $data_array) {
				foreach($data_array as $object_id => $data) {
					wp_localize_script($file_id, $object_id, $data);
				}
			}
			
			foreach($this->css_files as $id => $path) {
				wp_enqueue_style($id, $path);
			}
		}
		
		public function add_javascript($id, $path) {
			if(isset($this->javascript_files[$id])) {
				//METODO: error message
			}
			$this->javascript_files[$id] = $path;
			
			return $this;
		}
		
		public function add_javascripts($scripts) {
			foreach($scripts as $id => $path) {
				$this->add_javascript($id, $path);
			}
			
			return $this;
		}
		
		public function add_javascript_data($id, $object_name, $data) {
			//echo("\OddCore\Admin\Pages\Page::add_javascript_data<br />");
			
			if(!isset($this->javascript_data[$id])) {
				//METODO: check that a script exists with that id
				$this->javascript_data[$id] = array();
			}
			$this->javascript_data[$id][$object_name] = $data;
			
			return $this;
		}
		
		public function add_css($id, $path) {
			if(isset($this->css_files[$id])) {
				//METODO: error message
			}
			$this->css_files[$id] = $path;
			
			return $this;
		}
		
		public function output_after_title() {
			//echo("\OddCore\Admin\CustomPostTypes\CustomPostTypePost::output_after_title<br />");
			
			global $post;
			
			foreach($this->_meta_boxes_after_title as $meta_box) {
				$meta_box->output_with_nonce($post);
			}
		}
		
		public function output_after_editor() {
			//echo("\OddCore\Admin\CustomPostTypes\CustomPostTypePost::output_after_editor<br />");
			
			global $post;
			
			foreach($this->_meta_boxes_after_editor as $meta_box) {
				$meta_box->output_with_nonce($post);
			}
		}
		
		public function verify_and_save($post_id) {
			//echo("\OddCore\Admin\CustomPostTypes\CustomPostTypePost::verify_and_save<br />");
			
			foreach($this->_meta_boxes_after_title as $meta_box) {
				$meta_box->verify_and_save($post_id);
			}
			foreach($this->_meta_boxes_after_editor as $meta_box) {
				$meta_box->verify_and_save($post_id);
			}
		}
		
		public static function test_import() {
			echo("Imported \OddCore\Admin\CustomPostTypes\CustomPostTypePost<br />");
		}
	}
?>