<?php

if (!defined('ABSPATH')) {
	exit;
}

use Multi_Emails_WooCommerce\Utils;

$settings = Utils::get_settings();

$post_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

$email_recipients = Utils::get_multi_recipient_settings();
if (isset($post_data['email-recipients'])) {
	$email_recipients = $post_data['email-recipients'];
}

$email_recipients = array_map(function ($recipient_item) {
	return Utils::sanitize_recipient($recipient_item);
}, $email_recipients);

$customer_emails = $settings['customer_emails'];
if (!is_array($customer_emails)) {
	$customer_emails = [];
}

$customer_emails_items = array_map(function ($item) {
	return sprintf(
		'<li>
            <input placeholder="%s" class="regular-text" type="text" name="customer-emails[]" value="%s" /> 
            <span class="dashicons dashicons-remove remove-email"></span>
        </li>',
		__('Field title', 'multi-emails-woocommerce'),
		esc_attr($item)
	);
}, $customer_emails);

$additional_email_pages = Utils::get_additional_email_pages();

$kses_allow_options = array(
	'option' => array('value' => true, 'selected' => true)
);


?>

<div id="poststuff">

	<form method="post">
		<div class="postbox">

			<div class="postbox-header">
				<h2 class="hndle ui-sortable-handle"><?php esc_html_e('Multiple Email Settings', 'multi-emails-woocommerce'); ?> </h2>
			</div>

			<div class="inside">

				<?php wp_nonce_field('_nonce_multi_emails_woocommerce_settings'); ?>

				<table class="form-table">
					<tr>
						<th>
							<label><?php esc_html_e('Company Recipient(s)', 'multi-emails-woocommerce'); ?></label>
							<span class="multi-emails-woocommerce-tooltip">
								<span class="dashicons dashicons-editor-help"></span>
								<span class="tooltiptext"><?php esc_html_e('Enter any email to deliver cart order for fullfillment using any category or product.', 'multi-emails-woocommerce'); ?></span>
							</span>
						</th>
						<td>

							<?php

							foreach ($email_recipients as $index_no => $recipient_item) {
								$item_no = $index_no + 1;

								$recipient_item = wp_parse_args($recipient_item, array(
									'emails' => '',
									'categories' => [],
									'products' => [],
									'store_address' => '',
									'store_address_2' => '',
									'store_city' => '',
									'store_postcode' => '',
									'store_country' => '',
									'editing' => 'no',
								));

								printf('<fieldset class="woocommerce-multi-emails-fieldset" data-no="%d">', absint($item_no));
								echo '<span class="dashicons dashicons-remove remove-recipient"></span>';

								printf(
									'<div class="field-row"><input placeholder="%s" name="email-recipients[%d][emails]" type="email" value="%s"></div>',
									esc_html__('Email address(es)', 'multi-emails-woocommerce'),
									absint($item_no),
									esc_attr($recipient_item['emails'])
								);

								echo '<div class="field-row">';
								printf('<select class="multil-emails-woocommerce-recipient-categoires" name="email-recipients[%s][categories][]" multiple>', absint($item_no));
								echo wp_kses($this->get_categories($recipient_item['categories']), $kses_allow_options);
								echo '</select>';
								echo '</div>';

								echo '<div class="field-row">';
								printf('<select class="multi-emails-woocommerce-search-product" name="email-recipients[%d][products][]" multiple>', absint($item_no));
								echo wp_kses($this->get_products($recipient_item['products']), $kses_allow_options);
								echo '</select>';
								echo '</div>';

								$address_group_class = 'store-address-field-group';
								if ('yes' === $recipient_item['editing']) {
									$address_group_class .= ' editing-address';
								}

								echo '<div class="' . esc_attr($address_group_class) . '">';
								echo '<div class="field-row">';
								printf(
									'<input type="text" name="email-recipients[%s][store_address]" value="%s" placeholder="%s">',
									absint($item_no),
									esc_html($recipient_item['store_address']),
									esc_html__('Address line 1', 'multi-emails-woocommerce')
								);
								echo '</div>';

								echo '<div class="field-row">';
								printf(
									'<input type="text" name="email-recipients[%s][store_address_2]" value="%s" placeholder="%s" >',
									absint($item_no),
									esc_html($recipient_item['store_address_2']),
									esc_html__('Address line 2', 'multi-emails-woocommerce')
								);
								echo '</div>';

								echo '<div class="field-row">';
								printf(
									'<input type="text" name="email-recipients[%s][store_city]" value="%s" placeholder="%s" >',
									absint($item_no),
									esc_html($recipient_item['store_city']),
									esc_html__('City', 'multi-emails-woocommerce')
								);
								echo '</div>';

								$country_setting = (string) $recipient_item['store_country'];

								if (strstr($country_setting, ':')) {
									$country_setting = explode(':', $country_setting);
									$country         = current($country_setting);
									$state           = end($country_setting);
								} else {
									$country = $country_setting;
									$state   = '*';
								}
							?>

								<div class="field-row">
									<select name="email-recipients[<?php echo absint($item_no); ?>][store_country]" data-placeholder="<?php esc_attr_e('Choose a country / region&hellip;', 'multi-emails-woocommerce'); ?>" class="wc-enhanced-select">
										<option value="store_country"><?php esc_attr_e('Choose a country / region', 'multi-emails-woocommerce'); ?></option>
										<?php WC()->countries->country_dropdown_options($country, $state); ?>
									</select>
								</div>

							<?php

								echo '<div class="field-row">';
								printf(
									'<input type="text" name="email-recipients[%s][store_postcode]" value="%s" placeholder="%s" >',
									absint($item_no),
									esc_html($recipient_item['store_postcode']),
									esc_html__('Postcode / ZIP', 'multi-emails-woocommerce')
								);
								echo '</div>';

								printf(
									'<input class="email-recipient-editing-input" type="hidden" name="email-recipients[%s][editing]" value="%s" />',
									absint($item_no),
									esc_html($recipient_item['editing'])
								);

								printf('<a class="btn-address-view" href="#" data-show="%s" data-hide="%s"></a>', esc_attr__("Enter Optional 'Shipped From' Address", 'multi-emails-woocommerce'), esc_attr__('Hide Address Field', 'multi-emails-woocommerce'));
								echo '</div>';

								echo '</fieldset>';
							}

							?>

							<button class="button" id="woocommerce-multi-emails-add-recipient"><?php esc_html_e('Add Recipient', 'multi-emails-woocommerce'); ?></button>
						</td>
					</tr>

					<tr>
						<th>
							<label>
								<?php esc_html_e('Additional Email Notifications', 'multi-emails-woocommerce'); ?>
							</label>
						</th>

						<td>
							<label>
								<input type="checkbox" name="enable_addtional_email_notifications" value="yes" <?php checked('yes', $settings['enable_addtional_email_notifications']); ?>>
								<?php esc_html_e('Enable this option for additional customer email notifications.', 'multi-emails-woocommerce'); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th>
							<label>
								<?php esc_html_e('Customer Email Labels', 'multi-emails-woocommerce'); ?>
							</label>

							<span class="multi-emails-woocommerce-tooltip">
								<span class="dashicons dashicons-editor-help"></span>
								<span class="tooltiptext"><?php esc_html_e("Please enter the label of the email field, it will display in the user's account and checkout.", 'multi-emails-woocommerce'); ?></span>
							</span>
						</th>

						<td>
							<?php

							$email_kses_allowed = array(
								'li' => array(),
								'input' => array('placeholder' => true, 'class' => true, 'type' => true, 'name' => true, 'value' => true),
								'span' => array('class' => true),
							);

							?>
							<ul id="multi-emails-woocommerce-customer-emails"><?php echo wp_kses(implode("\n", $customer_emails_items), $email_kses_allowed); ?></ul>
							<a id="multi-emails-woocommerce-add-customer-email" href="#" class="button"><?php esc_html_e('Add email field', 'multi-emails-woocommerce'); ?></a>
						</td>
					</tr>

					<tr>
						<th>
							<label>
								<?php esc_html_e('Display Additional Email Fields', 'multi-emails-woocommerce'); ?>
							</label>
						</th>

						<td>
							<div class="input-inline-row">
								<label>
									<input type="checkbox" name="additional_email_pages[]" value="account" <?php checked(true, in_array('account', $additional_email_pages)); ?>>
									<?php esc_html_e('Account page', 'multi-emails-woocommerce'); ?>
								</label>

								<label>
									<input type="checkbox" name="additional_email_pages[]" value="checkout" <?php checked(true, in_array('checkout', $additional_email_pages)); ?>>
									<?php esc_html_e('Checkout', 'multi-emails-woocommerce'); ?>
								</label>
							</div>
						</td>
					</tr>

					<tr>
						<th>
							<?php esc_html_e('Product Order Conflict Notice', 'multi-emails-woocommerce'); ?>
						</th>

						<td>
							<label>
								<input type="checkbox" name="order_conflict_notice" value="yes" <?php checked('yes', $settings['order_conflict_notice_deactivate']); ?>>
								<?php esc_html_e('Deactivate conflict notice', 'multi-emails-woocommerce'); ?>

								<span class="multi-emails-woocommerce-tooltip">
									<span class="dashicons dashicons-editor-help"></span>
									<span class="tooltiptext"><?php esc_html_e('The text notice is injected in the cart if an item is selected that is not of the same vendor as the other item(s) already selected.', 'multi-emails-woocommerce'); ?></span>
								</span>
							</label>

							<div id="order-conflict-notice-text-field">
								<?php wp_editor($settings['order_conflict_notice_text'], 'order_conflict_notice_text', array(
									'textarea_rows' => 4
								)); ?>
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<?php submit_button(); ?>
	</form>

</div>

<script type="text/html" id="tmpl-woocommerce-multi-emails-recipient">
	<fieldset class="woocommerce-multi-emails-fieldset" data-no="{{data.index_no}}">

		<span class="dashicons dashicons-remove remove-recipient"></span>

		<div class="field-row">
			<input placeholder="<?php esc_html_e('Email address(es)'); ?>" name="email-recipients[{{data.index_no}}][emails]" type="email">
		</div>

		<div class="field-row">
			<select class="multil-emails-woocommerce-recipient-categoires" name="email-recipients[{{data.index_no}}][categories][]" multiple placeholder="<?php esc_html_e('Search for category'); ?>">
				<?php echo wp_kses($this->get_categories(), $kses_allow_options); ?>
			</select>
		</div>

		<div class="field-row">
			<select class="multi-emails-woocommerce-search-product" name="email-recipients[{{data.index_no}}][products][]" multiple>
				<?php echo wp_kses($this->get_products(), $kses_allow_options); ?>
			</select>
		</div>

		<div class="store-address-field-group">
			<div class="field-row">
				<input type="text" name="email-recipients[{{data.index_no}}][store_address]" placeholder="<?php esc_html_e('Address line 1', 'multi-emails-woocommerce'); ?>">
			</div>

			<div class="field-row">
				<input type="text" name="email-recipients[{{data.index_no}}][store_address_2]" placeholder="<?php esc_html_e('Address line 2', 'multi-emails-woocommerce'); ?>">
			</div>

			<div class="field-row">
				<input type="text" name="email-recipients[{{data.index_no}}][store_city]" placeholder="<?php esc_html_e('City', 'multi-emails-woocommerce'); ?>">
			</div>

			<div class="field-row">
				<select name="email-recipients[{{data.index_no}}][store_country]" data-placeholder="<?php esc_attr_e('Choose a country / region&hellip;', 'multi-emails-woocommerce'); ?>" class="wc-enhanced-select">
					<option value="store_country"><?php esc_attr_e('Choose a country / region', 'multi-emails-woocommerce'); ?></option>
					<?php WC()->countries->country_dropdown_options(); ?>
				</select>
			</div>

			<div class="field-row">
				<input type="text" name="email-recipients[{{data.index_no}}][store_postcode]" placeholder="<?php esc_html_e('Postcode / ZIP', 'multi-emails-woocommerce'); ?>">
			</div>

			<a class="btn-address-view" href="#" data-show="<?php esc_attr_e("Enter Optional 'Shipped From' Address", 'multi-emails-woocommerce'); ?>" data-hide="<?php esc_html_e('Hide Address Field', 'multi-emails-woocommerce'); ?>"></a>
		</div>

	</fieldset>
</script>