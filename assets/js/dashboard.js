(function ($) {
    $('#multi-emails-woocommerce-feedback-notice').on('click', '.notice-dismiss', function (e) {
        e.preventDefault();
        $.post(multi_emails_woocommerce_dashboard.ajax_url, { action: 'multi_emails_woocommerce_handle_notice', type: 'dismiss' })
        $('#multi-emails-woocommerce-feedback-notice').remove();

    })

    $('#multi-emails-woocommerce-feedback-notice').on('click', '.btn-leave-feedback', function (e) {
        $.post(multi_emails_woocommerce_dashboard.ajax_url, { action: 'multi_emails_woocommerce_handle_notice', type: 'feedback' })
        $('#multi-emails-woocommerce-feedback-notice').remove();
    })

})(jQuery)

