<?php
defined('RY_WT_VERSION') OR exit('No direct script access allowed');

class RY_ECPay_Gateway_Webatm extends RY_ECPay_Gateway_Base {
	public $payment_type = 'WebATM';

	public function __construct() {
		$this->id = 'ry_ecpay_webatm';
		$this->has_fields = false;
		$this->order_button_text = __('Pay via WebATM', RY_WT::$textdomain);
		$this->method_title = __('ECPay WebATM', RY_WT::$textdomain);
		$this->method_description = '';

		$this->form_fields = include(RY_WT_PLUGIN_DIR . 'woocommerce/gateways/ecpay/includes/settings-ecpay-gateway-webatm.php');
		$this->init_settings();

		$this->title = $this->get_option('title');
		$this->description = $this->get_option('description');
		$this->min_amount = (int) $this->get_option('min_amount', 0);
		$this->max_amount = (int) $this->get_option('max_amount', 0);

		parent::__construct();
	}

	public function is_available() {
		if( 'yes' == $this->enabled && WC()->cart ) {
			$total = WC()->cart->get_displayed_subtotal();
			if( 'incl' === WC()->cart->tax_display_cart ) {
				$total = round($total - (WC()->cart->get_cart_discount_total() + WC()->cart->get_cart_discount_tax_total()), wc_get_price_decimals());
			} else {
				$total = round($total - WC()->cart->get_cart_discount_total(), wc_get_price_decimals());
			}

			if( $this->min_amount > 0 and $total < $this->min_amount ) {
				return false;
			}
			if( $this->max_amount > 0 and $total > $this->max_amount ) {
				return false;
			}
		}

		return parent::is_available();
	}

	public function process_payment($order_id) {
		$order = wc_get_order($order_id);
		$order->add_order_note(__('Pay via ECPay WebATM', RY_WT::$textdomain));
		wc_reduce_stock_levels($order_id);

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url(true),
		);
	}
}
