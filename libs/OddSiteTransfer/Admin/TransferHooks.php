<?php
	namespace OddSiteTransfer\Admin;
	
	use \WP_Query;
	
	// \OddSiteTransfer\Admin\TransferHooks
	class TransferHooks {
		
		
		
		function __construct() {
			//echo("\OddSiteTransfer\Admin\TransferHooks::__construct<br />");
			
			
		}
		
		public function register() {
			//echo("\OddSiteTransfer\Admin\TransferHooks::register<br />");
			
			add_action('save_post', array($this, 'hook_save_post'), 10, 3);
			//METODO: delete post
			add_action('created_term', array($this, 'hook_created_term'), 10, 3);
			add_action('edited_term', array($this, 'hook_edited_term'), 10, 3);
			add_action('delete_term', array($this, 'hook_delete_term'), 10, 3);
			
			add_action('admin_notices', array($this, 'hook_admin_notices'));
		}
		
		public function hook_save_post($post_id, $post, $update) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_save_post<br />");
			
			if(wp_is_post_revision($post_id)) {
				return;
			}
			
			remove_action('save_post', array($this, 'hook_save_post'));
			
			$sync_index_target = get_post_meta($post_id, '_odd_server_transfer_sync_index_target', true);
			
			if($sync_index_target) {
				update_post_meta($post_id, '_odd_server_transfer_sync_index_target', intval($sync_index_target)+1);
			}
			else {
				update_post_meta($post_id, '_odd_server_transfer_sync_index_target', 1);
			}
		}
		
		public function hook_created_term($term_id, $tt_id, $taxonomy) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_created_term<br />");
			
			//METODO
		}
		
		public function hook_edited_term($term_id, $tt_id, $taxonomy) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_edited_term<br />");
			
			//METODO
		}
		
		public function hook_delete_term($term_id, $tt_id, $taxonomy) {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_delete_term<br />");
			
			//METODO
		}
		
		public function hook_admin_notices() {
			//echo("\OddSiteTransfer\Admin\TransferHooks::hook_admin_notices<br />");
			
			$screen = get_current_screen();
			//var_dump($screen);
			
			if($screen->base === 'post') {
				global $post;
				//var_dump($post);
				
				$sync_index = intval(get_post_meta($post->ID, '_odd_server_transfer_sync_index', true));
				$sync_index_target = intval(get_post_meta($post->ID, '_odd_server_transfer_sync_index_target', true));
				
				if($sync_index_target > $sync_index) {
					
					$element_id = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
					?>
						
						<div class="notice" id="<?php echo($element_id); ?>">
							<script type="text/javascript">
								window.OA.reactModuleCreator.createModule("checkSyncNotice", document.getElementById("<?php echo($element_id); ?>"), {"id": "<?php echo($post->ID); ?>", "transferUrl": "<?php echo(get_home_url()) ?>/wp-json/odd-site-transfer/v1/post/<?php echo($post->ID); ?>/transfer"});
							</script>
						</div>
						
					<?php
				}
			}
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\Admin\TransferHooks<br />");
		}
	}
?>