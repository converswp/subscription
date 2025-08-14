# Split Payment System - Hooks & Filters Reference

## ⚙️ Action Hooks & Filters (subscrpt_ Prefix)

Below is a curated list of useful developer hooks to extend or automate split payment logic:

## 🔧 Actions

| Hook Name | Trigger | Purpose | Parameters |
|-----------|---------|---------|------------|
| `subscrpt_split_payment_created` | When a new split payment plan is created | Log subscription, sync to CRM | `$subscription_id`, `$split_payment_args`, `$order_item` |
| `subscrpt_split_payment_completed` | When the final installment is paid | Trigger access, send license or mark course as complete | `$subscription_id`, `$payments_made`, `$max_payments` |
| `subscrpt_split_payment_renewed` | After each successful payment | Update usage, notify customer | `$subscription_id`, `$order_id`, `$order_item_id` |
| `subscrpt_split_payment_failed` | When a payment fails | Trigger fallback, retry, or notify | `$subscription_id` |
| `subscrpt_split_payment_cancelled` | When customer cancels the split plan | Pause service or revoke access | `$subscription_id` |
| `subscrpt_split_payment_early_renew_triggered` | When customer triggers early renewal | Enable early delivery or unlock content | `$subscription_id`, `$new_order_id`, `$new_order_item_id` |

## 🧩 Filters

| Filter Name | Description | Parameters | Return |
|-------------|-------------|------------|--------|
| `subscrpt_split_payment_args` | Modify arguments like interval, price before creation | `$args`, `$order_item`, `$product` | Modified args array |
| `subscrpt_split_payment_total_override` | Dynamically override total number of installments | `$max_payments`, `$subscription_id`, `$product_id` | Modified max payments integer |
| `subscrpt_split_payment_expire_status` | Customize how status changes after final payment | `$expire_status`, `$subscription_id`, `$payments_made`, `$max_payments` | Modified status string |
| `subscrpt_split_payment_next_due_date` | Alter date logic for next installment | `$next_date`, `$subscription_id`, `$recurr_timing`, `$payment_type` | Modified timestamp |
| `subscrpt_split_payment_disable_cancel` | Programmatically disable cancel button | `$disable_cancel`, `$subscription_id`, `$status` | Boolean |
| `subscrpt_split_payment_button_text` | Customize "Early Renew" or payment action buttons | `$label`, `$button_type`, `$subscription_id`, `$status` | Modified button text |

## 📝 Usage Examples

### Action Hook Examples

#### Log New Split Payment Plans
```php
add_action( 'subscrpt_split_payment_created', 'log_new_split_payment', 10, 3 );
function log_new_split_payment( $subscription_id, $split_payment_args, $order_item ) {
    error_log( "New split payment plan created: Subscription #{$subscription_id}" );
    
    // Sync to CRM
    $customer_email = $order_item->get_order()->get_billing_email();
    // send_to_crm( $customer_email, $split_payment_args );
}
```

#### Grant Access on Completion
```php
add_action( 'subscrpt_split_payment_completed', 'grant_full_access', 10, 3 );
function grant_full_access( $subscription_id, $payments_made, $max_payments ) {
    $user_id = get_post_field( 'post_author', $subscription_id );
    $product_id = get_post_meta( $subscription_id, '_subscrpt_product_id', true );
    
    // Grant premium access
    update_user_meta( $user_id, "full_access_{$product_id}", true );
    
    // Send completion email
    wp_mail( 
        get_userdata( $user_id )->user_email,
        'Payment Plan Complete!',
        'Congratulations! You have completed all payments and now have full access.'
    );
}
```

#### Track Each Payment
```php
add_action( 'subscrpt_split_payment_renewed', 'track_payment_progress', 10, 3 );
function track_payment_progress( $subscription_id, $order_id, $order_item_id ) {
    $payments_made = subscrpt_count_payments_made( $subscription_id );
    $remaining = subscrpt_get_remaining_payments( $subscription_id );
    
    // Send progress update email
    $order = wc_get_order( $order_id );
    $customer_email = $order->get_billing_email();
    
    wp_mail( 
        $customer_email,
        'Payment Received - Progress Update',
        "Payment #{$payments_made} received. {$remaining} payments remaining."
    );
}
```

#### Handle Payment Failures
```php
add_action( 'subscrpt_split_payment_failed', 'handle_payment_failure', 10, 1 );
function handle_payment_failure( $subscription_id ) {
    $user_id = get_post_field( 'post_author', $subscription_id );
    
    // Temporarily suspend access
    update_user_meta( $user_id, "access_suspended_{$subscription_id}", true );
    
    // Send retry email
    $user = get_userdata( $user_id );
    wp_mail( 
        $user->user_email,
        'Payment Failed - Action Required',
        'Your payment failed. Please update your payment method to continue.'
    );
}
```

### Filter Hook Examples

#### Modify Payment Plan Arguments
```php
add_filter( 'subscrpt_split_payment_args', 'customize_payment_args', 10, 3 );
function customize_payment_args( $args, $order_item, $product ) {
    // VIP customers get extended payment terms
    $order = $order_item->get_order();
    $customer_email = $order->get_billing_email();
    
    if ( is_vip_customer( $customer_email ) ) {
        $args['max_payments'] = $args['max_payments'] + 2; // Extra payments for VIP
    }
    
    return $args;
}
```

#### Dynamic Payment Limits
```php
add_filter( 'subscrpt_split_payment_total_override', 'dynamic_payment_limits', 10, 3 );
function dynamic_payment_limits( $max_payments, $subscription_id, $product_id ) {
    $user_id = get_post_field( 'post_author', $subscription_id );
    $user_level = get_user_meta( $user_id, 'membership_level', true );
    
    // Premium members get more payment options
    if ( $user_level === 'premium' ) {
        return $max_payments * 2;
    }
    
    return $max_payments;
}
```

#### Custom Completion Status
```php
add_filter( 'subscrpt_split_payment_expire_status', 'custom_completion_status', 10, 4 );
function custom_completion_status( $expire_status, $subscription_id, $payments_made, $max_payments ) {
    // Keep subscription active instead of expired for course access
    $product_id = get_post_meta( $subscription_id, '_subscrpt_product_id', true );
    $product_category = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'slugs' ) );
    
    if ( in_array( 'online-courses', $product_category ) ) {
        return 'active'; // Keep active for course access
    }
    
    return $expire_status;
}
```

#### Disable Cancel for Certain Plans
```php
add_filter( 'subscrpt_split_payment_disable_cancel', 'disable_cancel_for_discounted', 10, 3 );
function disable_cancel_for_discounted( $disable_cancel, $subscription_id, $status ) {
    // Disable cancel for discounted payment plans
    $product_id = get_post_meta( $subscription_id, '_subscrpt_product_id', true );
    $is_discounted = get_post_meta( $product_id, '_discounted_split_plan', true );
    
    if ( $is_discounted ) {
        return true; // Disable cancel button
    }
    
    return $disable_cancel;
}
```

#### Custom Button Text
```php
add_filter( 'subscrpt_split_payment_button_text', 'custom_button_text', 10, 4 );
function custom_button_text( $label, $button_type, $subscription_id, $status ) {
    switch ( $button_type ) {
        case 'early-renew':
            return 'Pay Next Installment Early';
        case 'cancel':
            return 'End Payment Plan';
        case 'renew':
            return 'Restart Payment Plan';
    }
    
    return $label;
}
```

## 📍 Hook Locations

### Actions are fired in:
- `subscrpt_split_payment_created`: `includes/Illuminate/Helper.php` - `process_new_subscription_order()`
- `subscrpt_split_payment_completed`: `includes/functions.php` - `subscrpt_is_max_payments_reached()`
- `subscrpt_split_payment_renewed`: `includes/Illuminate/Helper.php` - `process_order_renewal()`
- `subscrpt_split_payment_failed`: `subscription-pro/includes/Api/SubscriptionAction.php`
- `subscrpt_split_payment_cancelled`: `includes/Illuminate/Action.php` - `cancelled()`
- `subscrpt_split_payment_early_renew_triggered`: `subscription-pro/includes/Illuminate/Helper.php` - `create_early_renewal_history()`

### Filters are applied in:
- `subscrpt_split_payment_args`: `includes/Illuminate/Helper.php` - `process_new_subscription_order()`
- `subscrpt_split_payment_total_override`: `includes/functions.php` - `subscrpt_is_max_payments_reached()`
- `subscrpt_split_payment_expire_status`: `includes/functions.php` - `subscrpt_is_max_payments_reached()`
- `subscrpt_split_payment_next_due_date`: `includes/Illuminate/Order.php` - `generate_dates_for_subscription()`
- `subscrpt_split_payment_disable_cancel`: `includes/Frontend/MyAccount.php` - `view_subscrpt_content()`
- `subscrpt_split_payment_button_text`: `includes/Frontend/MyAccount.php` & `subscription-pro/includes/Frontend/Account.php`

---

*These hooks provide comprehensive control over the split payment system behavior and user experience.* 