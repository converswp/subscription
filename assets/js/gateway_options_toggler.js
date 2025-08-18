jQuery(document).ready(function () {
  const paypalTestMode = jQuery("#woocommerce_wp_subscription_paypal_testmode");

  makePaypalTestModeChange();
  paypalTestMode.change(makePaypalTestModeChange);

  function makePaypalTestModeChange() {
    if (paypalTestMode.is(":checked")) {
      // Base
      jQuery(".wpsubs-paypal-live-creds").hide();
      jQuery(".wpsubs-paypal-sandbox-creds").show();

      // Titles
      jQuery(".wc-settings-sub-title.wpsubs-paypal-live-creds").hide();
      jQuery(".wc-settings-sub-title.wpsubs-paypal-sandbox-creds").show();

      // Input fields
      jQuery("input.wpsubs-paypal-live-creds").parent().parent().parent().hide();
      jQuery("input.wpsubs-paypal-sandbox-creds").parent().parent().parent().show();

      // Text areas
      jQuery("textarea.wpsubs-paypal-live-creds").parent().parent().parent().hide();
      jQuery("textarea.wpsubs-paypal-sandbox-creds").parent().parent().parent().show();

      // Selects
      jQuery("select.wpsubs-paypal-live-creds").parent().parent().parent().hide();
      jQuery("select.wpsubs-paypal-sandbox-creds").parent().parent().parent().show();
    } else {
      // Base
      jQuery(".wpsubs-paypal-live-creds").show();
      jQuery(".wpsubs-paypal-sandbox-creds").hide();

      // Titles
      jQuery(".wc-settings-sub-title.wpsubs-paypal-live-creds").show();
      jQuery(".wc-settings-sub-title.wpsubs-paypal-sandbox-creds").hide();

      // Input fields
      jQuery("input.wpsubs-paypal-live-creds").parent().parent().parent().show();
      jQuery("input.wpsubs-paypal-sandbox-creds").parent().parent().parent().hide();

      // Text areas
      jQuery("textarea.wpsubs-paypal-live-creds").parent().parent().parent().show();
      jQuery("textarea.wpsubs-paypal-sandbox-creds").parent().parent().parent().hide();

      // Selects
      jQuery("select.wpsubs-paypal-live-creds").parent().parent().parent().show();
      jQuery("select.wpsubs-paypal-sandbox-creds").parent().parent().parent().hide();
    }
  }
});
