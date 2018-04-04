<?php
	namespace OddSiteTransfer\SiteTransfer\Encoders;
	
	use \WP_Post;
	use \OddSiteTransfer\SiteTransfer\Encoders\AcfPostEncoder;
	
	// \OddSiteTransfer\SiteTransfer\Encoders\WcOrderPostEncoder
	class WcOrderPostEncoder extends AcfPostEncoder {
		
		function __construct() {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\WcOrderPostEncoder::__construct<br />");
			
			parent::__construct();
		}
		
		protected function encode_meta_data($object, &$return_object) {
			//echo("\OddSiteTransfer\SiteTransfer\Encoders\WcOrderPostEncoder::encode_meta_data<br />");
			
			parent::encode_meta_data($object, $return_object);
			
			$order = wc_get_order( $object->ID );
			
			$raw_order_data = $order->get_data();
			
			$order_customer_linked_id = ost_get_user_transfer_id(get_user_by('id', $raw_order_data['customer_id']));
			$this->add_dependency('user', $order_customer_linked_id, $return_object['dependencies']);
			
			$order_data = array(
				'billing' => $raw_order_data['billing'],
				'shipping' => $raw_order_data['shipping'],
				'customer_id' => $order_customer_linked_id
			);
			
			//METODO: more fields
			
			$items_data = array();
			foreach ($order->get_items() as $item_key => $item_values) {
				
				$linked_post = get_post($item_values->get_product_id());
				$linked_post_id = ost_get_post_transfer_id($linked_post);
				$this->add_dependency('post', $linked_post_id, $return_object['dependencies']);
				
				$item_data = $item_values->get_data();
				
				$items_data[] = array(
					'id' => $linked_post_id,
					'sku' => $item_values->get_product()->get_sku(),
					'quantity' => $item_data['quantity']
				);
				
				//METODO: items can have many more fields
			}
			
			$return_object['meta_data']['woocommerce'] = array(
				'data' => $order_data,
				'items' => $items_data,
			);
		}
		
		public static function test_import() {
			echo("Imported \OddSiteTransfer\SiteTransfer\Encoders\WcOrderPostEncoder<br />");
		}
	}
?>