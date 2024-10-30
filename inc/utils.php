<?php

namespace Multi_Emails_WooCommerce;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Utilities class
 */
class Utils {

	/**
	 * Get settings of multi-email woocommerce
	 * 
	 * @since 1.0.1
	 */
	public static function get_settings() {
		$settings = get_option('multi_email_woocommerce_settings');

		return wp_parse_args($settings, array(
			'customer_emails' => [],
			'enable_addtional_email_notifications' => 'no',
			'additional_email_pages' => ['account', 'checkout'],
			'order_conflict_notice_deactivate' => 'no',
			'order_conflict_notice_text' => 'Items selected in your cart that include a custom shipping origin must be completed separately from additional items that might be ordered. The following categories and items can be included in your order: [category_and_product_links]. Please finalize this special order and then create a new order for additional items.',
		));
	}

	/**
	 * Sanitize recipient data
	 * 
	 * @since 1.0.0
	 */
	public static function sanitize_recipient($item) {
		if (!isset($item['emails'])) {
			$item['emails'] = '';
		}

		if (!isset($item['categories']) || !is_array($item['categories'])) {
			$item['categories'] = [];
		}

		if (!isset($item['products']) || !is_array($item['products'])) {
			$item['products'] = [];
		}

		$item['emails'] = trim($item['emails']);

		return $item;
	}

	/**
	 * Get multi recipient
	 * 
	 * @return array
	 */
	public static function get_multi_recipient_settings($has_addreess = false) {
		$email_recipients = get_option('multi-emails-woocommerce-recipients');
		if (!is_array($email_recipients)) {
			$email_recipients = [];
		}

		$recipients = array_map(function ($recipient_item) {
			return Utils::sanitize_recipient($recipient_item);
		}, $email_recipients);

		$email_recipients = array();
		foreach ($recipients as $recipient_id => $recipient) {
			if (!empty($recipient['store_address']) && !empty($recipient['store_city']) && !empty($recipient['store_country']) && !empty($recipient['store_postcode'])) {
				$email_recipients[$recipient_id] = $recipient;
			}
		}

		foreach ($email_recipients as $recipient_id => $recipient_item) {
			$recipient_categories = $recipient_item['categories'];

			foreach ($recipient_categories as $recipient_term_id) {
				$get_child_terms = get_terms(array(
					'taxonomy' => 'product_cat',
					'child_of' => $recipient_term_id
				));

				foreach ($get_child_terms as $term) {
					$recipient_categories[] = $term->term_id;
				}
			}

			$email_recipients[$recipient_id]['all_categories'] = array_map('absint', $recipient_categories);
		}

		return $email_recipients;
	}

	/**
	 * Get addtional email key
	 * 
	 * @since 1.0.0
	 * @return string
	 */
	public static function get_additional_email_key($number) {
		return 'mew_billing_email_' . $number;
	}

	/** 
	 * Get additional email fields
	 * 
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_additional_email_fields() {
		$customer_emails = self::get_settings()['customer_emails'];
		if (!is_array($customer_emails) || empty($customer_emails)) {
			$customer_emails = [];
		}

		$addtional_emails = [];

		$start = 0;
		foreach ($customer_emails as $field_label) {
			$start++;
			$key = self::get_additional_email_key($start);

			if (empty($field_label)) {
				$field_label = __('Email address', 'multi-emails-woocommerce') . ' ' . ($start + 1);
			}

			$addtional_emails[$key] = $field_label;
		}

		return $addtional_emails;
	}

	/**
	 * Get additional email page settings
	 * 
	 * @since 1.0.0
	 * @return array
	 */

	public static function get_additional_email_pages() {
		$additional_email_pages = self::get_settings()['additional_email_pages'];
		if (!is_array($additional_email_pages)) {
			$additional_email_pages = [];
		}

		return $additional_email_pages;
	}

	/**
	 * Get recipient from cart
	 * 
	 * @since 1.0.0
	 * @param boolean $has_address 
	 * @return false|integer
	 */
	public static function get_recipient_from_cart($has_addreess = false) {
		$cart_items = WC()->cart->get_cart();

		$email_recipients = Utils::get_multi_recipient_settings($has_addreess);

		$matched_recipients = [];
		foreach ($email_recipients as $recipient_id => $recipient_item) {

			foreach ($cart_items as $key => $cart_item) {
				$cart_product_id = $cart_item['product_id'];

				$product_categories = get_the_terms($cart_product_id, 'product_cat');

				$term_ids = [];
				if ($product_categories) {
					$term_ids = wp_list_pluck($product_categories, 'term_id');
				}

				$matched_items = array_intersect($term_ids, $recipient_item['all_categories']);
				if (count($matched_items) > 0 || in_array($cart_product_id, $recipient_item['products'])) {
					$matched_recipients[] = $recipient_id;
				}
			}
		}

		$matched_recipients = array_unique($matched_recipients);
		if (count($matched_recipients) == 0) {
			return false;
		}

		$recipient_id = current($matched_recipients);

		return $email_recipients[$recipient_id];
	}

	/**
	 * Get company from product ID
	 * 
	 * @since 1.0.0
	 * @return false|array
	 */
	public static function get_company_from_product_id($product_id) {
		$email_recipients = self::get_multi_recipient_settings();

		$terms = get_the_terms($product_id, 'product_cat');

		$term_ids = [];
		if ($terms) {
			$term_ids = wp_list_pluck($terms, 'term_id');
		}

		$company = false;
		foreach ($email_recipients as $recipient_item) {
			$matched_items = array_intersect($term_ids, $recipient_item['categories']);
			if (count($matched_items) > 0 || in_array($product_id, $recipient_item['products'])) {
				$company = $recipient_item;
				break;
			}
		}

		return $company;
	}
}
