<?php

namespace Multi_Emails_WooCommerce;

if (!defined('ABSPATH')) {
	exit;
}


/**
 * Main class plugin
 */
final class Main {
	/**
	 * Hold the current instance
	 * 
	 * @var Main
	 */
	private static $instance = null;

	/**
	 * Get the instance of plugin
	 * 
	 * @since 1.0.0
	 * @return Main
	 */
	public static function get_instance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Throw error on object clone
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 * 
	 * @since 1.0
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong(__FUNCTION__, esc_html__('Cheating huh?', 'multi-emails-woocommerce'), '1.0.0');
	}

	/**
	 * Disable unserializing of the class
	 * 
	 * @since 1.0
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong(__FUNCTION__, esc_html__('Cheating huh?', 'multi-emails-woocommerce'), '1.0.0');
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		if (is_admin()) {
			require_once  MULTI_EMAILS_WOOCOMMERCE_PATH . '/inc/admin/admin.php';
		}

		add_filter('plugin_action_links', array($this, 'add_plugin_link'), 10, 2);
		add_filter('plugin_row_meta', array($this, 'add_donate_link'), 10, 2);
		add_action('woocommerce_checkout_order_processed', array($this, 'order_send_email'), 10, 3);
		add_action('woocommerce_new_order', array($this, 'save_additional_emails'), 10, 2);
		add_filter('woocommerce_billing_fields', array($this, 'add_frontend_fields'));
		add_filter('woocommerce_mail_callback_params', array($this, 'add_additional_emails'), 100, 2);
		add_filter('woocommerce_customer_meta_fields', array($this, 'admin_user_profile_emails'));

		add_filter('woocommerce_countries_base_address', array($this, 'change_store_base_address'));
		add_filter('woocommerce_countries_base_address_2', array($this, 'change_store_base_address_2'));
		add_filter('woocommerce_countries_base_country', array($this, 'change_store_base_country'));
		add_filter('woocommerce_countries_base_state', array($this, 'change_store_base_state'));
		add_filter('woocommerce_countries_base_city', array($this, 'change_store_base_city'));
		add_filter('woocommerce_countries_base_postcode', array($this, 'change_store_base_postcode'));
		add_filter('woocommerce_add_to_cart_validation', array($this, 'add_to_cart_validation'), 100, 2);
	}

	/**
	 * Add settings link at plugin action links
	 * 
	 * @since 1.0.0
	 * @return array
	 */
	public function add_plugin_link($plugin_actions, $plugin_file) {
		if (MULTI_EMAILS_WOOCOMMERCE_BASENAME == $plugin_file) {
			array_unshift($plugin_actions, sprintf('<a href="%s">%s</a>', menu_page_url('multi-emails-woocommerce', false), __('Settings', 'multi-emails-woocommerce')));
		}

		return $plugin_actions;
	}

	/**
	 * Add donate link at plugin
	 * 
	 * @since 1.0.0
	 * @return array
	 */
	public function add_donate_link($links, $plugin_file) {
		if (MULTI_EMAILS_WOOCOMMERCE_BASENAME == $plugin_file) {
			$links[] = '<a href="' . esc_url('https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=E7LS2JGFPLTH2') . '" target="_blank">' . esc_html__('Donation for Homeless', 'multi-emails-woocommerce') . '</a>';
		}

		return $links;
	}

	public function update_recipient($recipient) {
		return $this->recipient;
	}

	/**
	 * Send email after order
	 * 
	 * @since 1.0.0
	 */
	public function order_send_email($order_id, $posted_data, $order) {
		$email_recipients = Utils::get_multi_recipient_settings();

		$email_recipients = array_filter($email_recipients, function ($item) {
			return !empty($item['emails']);
		});


		$order_recipient_emails = [];

		foreach ($order->get_items() as $item) {
			$company = Utils::get_company_from_product_id($item->get_product_id());
			if ($company) {
				$order_recipient_emails[] = $company['emails'];
			}
		}

		$order_recipient_emails = array_unique($order_recipient_emails);

		add_filter('woocommerce_new_order_email_allows_resend', '__return_true');

		$wc_emails = WC()->mailer()->get_emails();


		foreach ($order_recipient_emails as $emails) {
			$this->recipient = $emails;
			add_filter('woocommerce_email_recipient_new_order', array($this, 'update_recipient'));
			WC()->mailer()->emails['WC_Email_New_Order']->trigger($order->get_id(), $order, true);
			remove_filter('woocommerce_email_recipient_new_order', array($this, 'update_recipient'));
		}

		remove_filter('woocommerce_new_order_email_allows_resend', '__return_true');

		delete_post_meta($order_id, '_new_order_email_sent');
	}

	/**
	 * Add addtional email field at checkout form
	 * 
	 * @since 1.0.0
	 * @return array
	 */
	public function add_frontend_fields($fields) {
		$additional_pages = Utils::get_additional_email_pages();

		if (is_checkout() && !in_array('checkout', $additional_pages)) {
			return $fields;
		}

		if (is_account_page() && !in_array('account', $additional_pages)) {
			return $fields;
		}


		$billing_email_priority = $fields['billing_email']['priority'];

		$start = 0;

		$additional_email_fields = Utils::get_additional_email_fields();
		foreach ($additional_email_fields as $field_key => $email_label) {
			$start++;
			$fields[$field_key] = array(
				'label'        => $email_label,
				'required'     => false,
				'class'        => ['form-row-wide'],
				'validate'     => ['email'],
				'type'         => 'email',
				'autocomplete' => 'none',
				'priority'     => $billing_email_priority + $start,
			);
		}

		return $fields;
	}

	/**
	 * Save additional email at order
	 * 
	 * @since 1.0.0
	 */
	public function save_additional_emails($order_id, $order) {
		$post_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
		$user_id = $order->get_customer_id();

		$additional_email_keys = array_keys(Utils::get_additional_email_fields());

		foreach ($additional_email_keys as $field_key) {
			$email = !empty($post_data[$field_key]) ? sanitize_email($post_data[$field_key]) : false;
			if (!is_email($email)) {
				continue;
			}

			if ($user_id && is_checkout()) {
				update_user_meta($user_id, $field_key, $email);
			}

			update_post_meta($order_id, $field_key, $email);
		}
	}

	/**
	 * Add additional recipients
	 * 
	 * @since 1.0.0
	 * @return array
	 */
	public function add_additional_emails($params, \WC_Email $email) {
		$enable_addtional_email_notifications = Utils::get_settings()['enable_addtional_email_notifications'];
		if (!$email->is_customer_email() || 'yes' !== $enable_addtional_email_notifications) {
			return $params;
		}

		$order = $email->object;
		if (!is_a($order, 'WC_Order')) {
			return $params;
		}

		$additional_recipients = array_map(function ($key) use ($order) {
			return sanitize_email(get_post_meta($order->get_id(), $key, true));
		}, array_keys(Utils::get_additional_email_fields()));

		$additional_recipients = array_unique(array_filter($additional_recipients, 'is_email'));
		array_unshift($additional_recipients, $params[0]);
		$params[0] = implode(',', $additional_recipients);
		return $params;
	}

	/**
	 * Add additional field on wordpress profile page
	 * 
	 * @since 1.0.0
	 * @return array
	 */
	public function admin_user_profile_emails($fields) {
		$additional_emails = Utils::get_additional_email_fields();

		foreach ($additional_emails as $meta_key => $field_label) {
			$fields['billing']['fields'][$meta_key] = [
				'label'       => $field_label,
				'description' => ''
			];
		}

		return $fields;
	}

	/**
	 * Change Woocommerce store address
	 * 
	 * @since 1.0.0
	 * @return string
	 */
	public function change_store_base_address($value) {
		if (is_admin() || is_null(WC()->cart)) {
			return $value;
		}

		$cart_items = WC()->cart->get_cart();
		if (count($cart_items) == 0) {
			return $value;
		}

		foreach ($cart_items as $key => $cart_item) {
			$company = Utils::get_company_from_product_id($cart_item['product_id']);

			if (!empty($company['store_address'])) {
				$value = $company['store_address'];
				break;
			}
		}

		return $value;
	}

	/**
	 * Change Woocommerce store address_2
	 * 
	 * @since 1.0.0
	 * @return string
	 */
	public function change_store_base_address_2($value) {
		if (is_admin() || is_null(WC()->cart)) {
			return $value;
		}

		$cart_items = WC()->cart->get_cart();
		if (count($cart_items) == 0) {
			return $value;
		}

		foreach ($cart_items as $key => $cart_item) {
			$company = Utils::get_company_from_product_id($cart_item['product_id']);

			if (!empty($company['store_address_2'])) {
				$value = $company['store_address_2'];
				break;
			}
		}

		return $value;
	}

	/**
	 * Change Woocommerce store country
	 * 
	 * @since 1.0.0
	 * @return string
	 */
	public function change_store_base_country($value) {
		if (is_admin() || is_null(WC()->cart)) {
			return $value;
		}

		$cart_items = WC()->cart->get_cart();
		if (count($cart_items) == 0) {
			return $value;
		}

		foreach ($cart_items as $key => $cart_item) {
			$company = Utils::get_company_from_product_id($cart_item['product_id']);

			if (!empty($company['store_country'])) {
				$store_country = explode(':', $company['store_country']);
				if (!empty($store_country[0]) && 'store_country' !== $company['store_country']) {
					$value = $store_country[0];
				}

				break;
			}
		}

		return $value;
	}

	/**
	 * Change Woocommerce store state
	 * 
	 * @since 1.0.0
	 * @return string
	 */
	public function change_store_base_state($value) {
		if (is_admin() || is_null(WC()->cart)) {
			return $value;
		}

		$cart_items = WC()->cart->get_cart();
		if (count($cart_items) == 0) {
			return $value;
		}

		foreach ($cart_items as $key => $cart_item) {
			$company = Utils::get_company_from_product_id($cart_item['product_id']);

			if (!empty($company['store_country'])) {
				$store_country = explode(':', $company['store_country']);
				if (!empty($store_country[1]) && 'store_country' !== $company['store_country']) {
					$value = $store_country[1];
				}

				break;
			}
		}

		return $value;
	}

	/**
	 * Change Woocommerce store city
	 * 
	 * @since 1.0.0
	 * @return string
	 */
	public function change_store_base_city($value) {
		if (is_admin() || is_null(WC()->cart)) {
			return $value;
		}

		$cart_items = WC()->cart->get_cart();
		if (count($cart_items) == 0) {
			return $value;
		}

		foreach ($cart_items as $key => $cart_item) {
			$company = Utils::get_company_from_product_id($cart_item['product_id']);
			if (!empty($company['store_city'])) {
				$value = $company['store_city'];
				break;
			}
		}

		return $value;
	}

	/**
	 * Change Woocommerce store postcode
	 * 
	 * @since 1.0.0
	 * @return string
	 */
	public function change_store_base_postcode($value) {
		if (is_admin() || is_null(WC()->cart)) {
			return $value;
		}

		$cart_items = WC()->cart->get_cart();
		if (count($cart_items) == 0) {
			return $value;
		}

		foreach ($cart_items as $key => $cart_item) {
			$company = Utils::get_company_from_product_id($cart_item['product_id']);

			if (!empty($company['store_postcode'])) {
				$value = $company['store_postcode'];
				break;
			}
		}

		return $value;
	}

	/**
	 * Check before add product to cart
	 * 
	 * @since 1.0.1
	 */
	public function add_to_cart_validation($valid, $product_id) {
		$settings = Utils::get_settings();

		$order_conflict_notice = $settings['order_conflict_notice_deactivate'];
		if ($order_conflict_notice == 'yes') {
			return $valid;
		}

		$cart_items = WC()->cart->get_cart();
		if (count($cart_items) == 0) {
			return $valid;
		}

		$cart_recipient = Utils::get_recipient_from_cart(true);
		if ($cart_recipient === false) {
			return $valid;
		}

		if (in_array($product_id, $cart_recipient['products'])) {
			return $valid;
		}

		$terms = get_the_terms($product_id, 'product_cat');

		$term_ids = [];
		if ($terms) {
			$term_ids = wp_list_pluck($terms, 'term_id');
		}

		$matched_items = array_intersect($term_ids, $cart_recipient['all_categories']);
		if (count($matched_items) > 0) {
			return $valid;
		}

		$company_items_link = [];
		foreach ($cart_recipient['categories'] as $term_id) {
			$term = get_term($term_id, 'product_cat');
			if (!is_a($term, 'WP_Term')) {
				continue;
			}

			$company_items_link[] = sprintf('<a target="_blank" href="%s">%s</a>', esc_url(get_term_link($term)), $term->name);
		}

		foreach ($cart_recipient['products'] as $product_id) {
			$_product = get_post($product_id);
			if (!is_a($_product, 'WP_Post')) {
				continue;
			}

			$company_items_link[] = sprintf('<a href="%s">%s</a>', esc_url(get_the_permalink($_product)), get_the_title($_product));
		}

		$notice = trim($settings['order_conflict_notice_text']);
		if (empty($notice)) {
			return false;
		}

		$company_items_link = array_unique($company_items_link);
		$notice = str_replace('[category_and_product_links]', implode(', ', $company_items_link), $notice);

		wc_add_notice($notice, 'error');

		return false;
	}
}

Main::get_instance();
