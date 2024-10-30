(function ($) {
	$('.multil-emails-woocommerce-recipient-categoires').select2({
		placeholder: multi_emails_woocommerce.i10n.search_category
	})

	$('.multi-emails-woocommerce-search-product').select2({
		placeholder: multi_emails_woocommerce.i10n.search_product
	})


	$('#woocommerce-multi-emails-add-recipient').on('click', function (e) {
		e.preventDefault();

		const total_recipients = $(this).closest('td').find('.woocommerce-multi-emails-fieldset').data('no') || 0;

		const add_recipient_template = wp.template('woocommerce-multi-emails-recipient');

		const recipient = $(add_recipient_template({ index_no: total_recipients + 1 })).insertBefore($(this))

		recipient.find('.multil-emails-woocommerce-recipient-categoires').select2({
			placeholder: multi_emails_woocommerce.i10n.search_category
		})

		recipient.find('.multi-emails-woocommerce-search-product').select2({
			placeholder: multi_emails_woocommerce.i10n.search_product
		})

		recipient.find('.wc-enhanced-select').selectWoo().addClass('enhanced');
	})

	$('body').on('click', '.woocommerce-multi-emails-fieldset .remove-recipient', function (e) {
		e.preventDefault();
		const response = confirm(multi_emails_woocommerce.i10n.delete_recipient_item)
		if (response) {
			$(this).closest('.woocommerce-multi-emails-fieldset').remove();
		}
	})

	$('#multi-emails-woocommerce-add-customer-email').on('click', function (e) {
		e.preventDefault();

		const customer_emails = $('#multi-emails-woocommerce-customer-emails')

		customer_emails.append(
			`<li>
                <input placeholder="${multi_emails_woocommerce.i10n.customer_field_title}" class="regular-text" type="text" name="customer-emails[]" /> 
                <span class="dashicons dashicons-remove remove-email"></span>
            </li>`
		)
	})

	$('#multi-emails-woocommerce-customer-emails').on('click', '.remove-email', function (e) {
		e.preventDefault();
		const response = confirm(multi_emails_woocommerce.i10n.customer_remove_email_notice)
		if (response) {
			$(this).closest('li').remove();
		}
	})

	$('body').on('click', '.woocommerce-multi-emails-fieldset .store-address-field-group .btn-address-view', function (e) {
		e.preventDefault();
		const address_container = $(this).closest('.store-address-field-group').toggleClass('editing-address')
		const editing_button_value = address_container.hasClass('editing-address') ? 'yes' : 'no';
		address_container.find('.email-recipient-editing-input').val(editing_button_value);
	})

	$('[name="order_conflict_notice"]').on('change', function () {
		if ($(this).is(':checked')) {
			$('#order-conflict-notice-text-field').hide()
		} else {
			$('#order-conflict-notice-text-field').show()
		}
	}).trigger('change')

})(jQuery)

