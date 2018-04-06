<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	use \WP_Post;
	use \OddSiteTransfer\SiteTransfer\Encoders\WcOrderPostEncoder;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\WcSubscriptionPostEncoder
	class WcSubscriptionPostEncoder extends WcOrderPostEncoder {
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\WcSubscriptionPostEncoder::__construct<br />");
			
			parent::__construct();
		}
		
		protected function encode_meta_data($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\WcSubscriptionPostEncoder::encode_meta_data<br />");
			
			parent::encode_meta_data($object, $return_object);
			
			$post_id = $object->ID;
			
			$subscription = new \WC_Subscription($post_id);
			
			$related_orders = array();
			
			$order_ids = $subscription->get_related_orders('ids', 'renewal');
			foreach($order_ids as $order_id) {
				$linked_post = get_post($order_id);
				$linked_post_id = ost_get_post_transfer_id($linked_post);
				$this->add_dependency('post', $linked_post_id, $return_object['dependencies']);
				$related_orders[] = $linked_post_id;
			}
			
			$properties = array();
			$properties['billing_period'] = $subscription->billing_period;
			$properties['billing_interval'] = $subscription->billing_interval;
			
			$return_object['meta_data']['woocommerce']['subscription'] = array(
				'properties' => $properties,
				'related_orders' => $related_orders
			);
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\WcSubscriptionPostEncoder<br />");
		}
	}
?>