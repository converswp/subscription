function wpSubsInstallPaypalIntegration() {
  jQuery
    .post(wpSubsIntegrations.ajax_url, {
      action_callback: "wp_subs_install_paypal_integration",
      nonce: wpSubsIntegrations.nonce,
    })
    .done(function (response) {
      console.log(response);

      if (response.success) {
        console.log("Option updated:", response.data);
      } else {
        console.error("Failed to update:", response.data);
      }
    })
    .fail(function () {
      console.error("AJAX request failed");
    });
}
