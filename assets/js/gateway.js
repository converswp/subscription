jQuery(document).ready(function ($) {
  function addPasswordToggle(inputId) {
    const $input = $("#" + inputId);
    if (!$input.length || $input.attr("type") !== "password") return;

    // Create wrapper span for layout
    const $wrapper = $("<span>").css({
      display: "inline-flex",
      alignItems: "center",
      gap: "0",
    });

    // Create Dashicon toggle button
    const $toggleBtn = $("<button>")
      .attr("type", "button")
      .addClass("dashicons dashicons-visibility")
      .css({
        border: $input.css("border"),
        borderRadius: $input.css("border-radius"),
        background: $input.css("background-color"),
        height: $input.height(),
        minHeight: "30px",
        width: "35px",
        margin: "0px 8px",
        fontSize: "18px",
        cursor: "pointer",
        lineHeight: 1,
      })
      .attr("title", "Show password");

    // Toggle functionality
    $toggleBtn.on("click", function () {
      const isPassword = $input.attr("type") === "password";
      $input.attr("type", isPassword ? "text" : "password");
      $toggleBtn
        .removeClass("dashicons-visibility dashicons-hidden")
        .addClass(isPassword ? "dashicons-hidden" : "dashicons-visibility")
        .attr("title", isPassword ? "Hide password" : "Show password");
    });

    // Wrap input and insert button
    $input.wrap($wrapper);
    $input.after($toggleBtn);
  }

  // Add toggles.
  addPasswordToggle("woocommerce_wp_subscription_paypal_client_id");
  addPasswordToggle("woocommerce_wp_subscription_paypal_client_secret");
});
