# Split Payment Hooks Reference

## Overview
This document provides a comprehensive reference for the Split Payment feature implementation in WP Subscription Pro. The split payment system allows customers to pay for subscriptions in installments rather than recurring payments.

## Current Implementation Status

### ✅ **COMPLETED FEATURES**

#### 1. Product Page Settings
- **Payment Type Selection**: Choose between 'recurring' and 'split_payment'
- **Number of Payments**: Set total installments (minimum 2)
- **Access Ends Timing**: Three options:
  - `after_last_payment`: Access ends immediately after final payment
  - `after_full_duration`: Access ends after full subscription duration
  - `custom_duration`: Custom access period after first payment
- **Custom Access Duration**: Time and type (days/weeks/months/years) for custom duration
- **Payment Failure Settings**: 
  - Maximum payment retries (0-10)
  - Grace period in days (0-30)

**Files**: 
- `includes/Admin/Product/Simple.php` (Simple products)
- `includes/Admin/Product/Variable.php` (Variable products)

#### 2. Order Page Integration
- **Split Payment Notes**: Orders automatically show split payment information
- **Payment Progress Tracking**: Shows current payment vs total payments
- **Order-Subscription Linking**: Proper relationship tracking between orders and subscriptions

**Files**: `includes/Illuminate/Helper.php`

#### 3. Subscription Admin Page
- **Split Payment Activities**: Complete payment history and status tracking
- **Payment Progress**: Shows payments made vs total required
- **Access Control Information**: Displays when access will end based on settings
- **Payment Failure Logging**: Tracks failed payments and retry attempts
- **Renewal Notes**: Comprehensive activity logging

**Files**: `includes/Illuminate/SplitPaymentHandler.php`

#### 4. User Subscription Page
- **Payment Progress Display**: Shows "X/Y payments completed"
- **Payment Type Indicator**: Clearly shows "Split Payment" vs "Recurring"
- **Remaining Payments**: Displays count of remaining installments
- **Access Duration Info**: Shows when access will end

**Files**: `templates/myaccount/single.php`

#### 5. WPS Settings Page
- **Split Payment Retry Settings**: Global configuration for payment retries
- **Grace Period Management**: Default grace period settings
- **Payment Failure Handling**: Comprehensive failure management system

**Files**: `includes/Admin/Settings.php`

#### 6. Reminder Emails
- **Payment Due Reminders**: Automated emails before next payment
- **Failure Notifications**: Alerts for failed payments
- **Completion Notifications**: Emails when split payment plan is completed

**Files**: `includes/Illuminate/Emails/RenewReminder.php`

#### 7. Core Split Payment Hooks
- **Payment Completion**: `subscrpt_split_payment_completed`
- **Payment Failure**: `subscrpt_split_payment_failed`
- **Arguments Filter**: `subscrpt_split_payment_args`
- **Total Override**: `subscrpt_split_payment_total_override`
- **Expire Status**: `subscrpt_split_payment_expire_status`
- **Next Due Date**: `subscrpt_split_payment_next_due_date`

**Files**: `includes/functions.php`, `includes/Illuminate/SplitPaymentHandler.php`

### 🔧 **CORE CLASSES & FUNCTIONS**

#### SplitPaymentHandler Class
```php
namespace SpringDevs\SubscriptionPro\Illuminate;

class SplitPaymentHandler {
    // Calculate access end dates
    public static function calculate_access_end_date($subscription_id)
    
    // Handle payment completion
    public static function handle_split_payment_completion($subscription_id, $payments_made, $max_payments)
    
    // Get payment dates
    public static function get_final_payment_date($subscription_id)
    public static function get_first_payment_date($subscription_id)
}
```

#### PaymentFailureHandler Class
```php
namespace SpringDevs\SubscriptionPro\Illuminate;

class PaymentFailureHandler {
    // Handle payment failures
    public static function handle_payment_failure($subscription_id)
    
    // Manage retries and grace periods
    public static function schedule_payment_retry($subscription_id, $failure_count)
    public static function start_grace_period($subscription_id, $grace_period_days)
}
```

#### Helper Functions
```php
// Check if max payments reached
function subscrpt_is_max_payments_reached($subscription_id)

// Get remaining payments
function subscrpt_get_remaining_payments($subscription_id)

// Get payment type
function subscrpt_get_payment_type($subscription_id)

// Count payments made
function subscrpt_count_payments_made($subscription_id)
```

### 📋 **IMPLEMENTATION DETAILS**

#### Product Meta Fields
- `_subscrpt_payment_type`: 'recurring' or 'split_payment'
- `_subscrpt_max_no_payment`: Number of total payments
- `_subscrpt_access_ends_timing`: When access ends
- `_subscrpt_custom_access_duration_time`: Custom duration value
- `_subscrpt_custom_access_duration_type`: Custom duration unit
- `_subscrpt_max_payment_retries`: Maximum retry attempts
- `_subscrpt_payment_grace_period`: Grace period in days

#### Subscription Meta Fields
- `_subscrpt_split_payment_completed_fired`: Prevents duplicate completion actions
- `_subscrpt_access_end_date`: Calculated access end timestamp
- `_subscrpt_payment_failure_count`: Current failure count
- `_subscrpt_grace_period_start`: Grace period start timestamp

#### Database Tables
- `wp_subscrpt_order_relation`: Links orders to subscriptions
- `wp_posts`: Subscription posts with custom post type 'subscrpt_order'
- `wp_comments`: Activity notes and payment history

### 🚀 **AVAILABLE HOOKS FOR DEVELOPERS**

#### Actions (do_action)
```php
// Split payment completed
do_action('subscrpt_split_payment_completed', $subscription_id, $payments_made, $max_payments);

// Payment failure logged
do_action('subscrpt_payment_failure_logged', $subscription_id, $failure_count);

// Payment failure notification sent
do_action('subscrpt_payment_failure_notification_sent', $subscription_id, $failure_count, $max_retries);

// Access suspended
do_action('subscrpt_access_suspended', $subscription_id);

// Access restored
do_action('subscrpt_access_restored', $subscription_id);
```

#### Filters (apply_filters)
```php
// Modify split payment arguments
$args = apply_filters('subscrpt_split_payment_args', $args, $order_item, $product);

// Override total payments
$max_payments = apply_filters('subscrpt_split_payment_total_override', $max_payments, $subscription_id, $product_id);

// Customize expire status
$expire_status = apply_filters('subscrpt_split_payment_expire_status', 'expired', $subscription_id, $payments_made, $max_payments);

// Modify next due date
$next_date = apply_filters('subscrpt_split_payment_next_due_date', $next_date, $subscription_id, $recurr_timing, $type);
```

### 📧 **EMAIL TEMPLATES**

#### Available Email Types
1. **Payment Due Reminders**: Sent before next payment date
2. **Payment Failure Notifications**: Alert customers of failed payments
3. **Completion Notifications**: Inform when split payment plan is finished
4. **Grace Period Warnings**: Notify before access suspension

#### Email Customization
- Templates located in `templates/emails/`
- Customizable subjects and content
- Placeholder support for dynamic data
- Triggered automatically via cron jobs

### ⚙️ **CONFIGURATION OPTIONS**

#### Global Settings
- **Default Payment Retries**: 3 attempts
- **Default Grace Period**: 7 days
- **Reminder Days**: 7 days before payment due
- **Email Notifications**: Enabled by default

#### Per-Product Settings
- **Payment Type**: Recurring or Split Payment
- **Installment Count**: 2-∞ payments
- **Access Timing**: Flexible access control
- **Failure Handling**: Custom retry and grace settings

### 🔍 **DEBUGGING & MONITORING**

#### Debug Logging
- Comprehensive error logging for payment failures
- Payment progress tracking
- Access timing calculations
- Retry attempt monitoring

#### Admin Notifications
- Payment failure alerts
- Completion status updates
- Access suspension notifications
- Grace period warnings

## �� **NEXT STEPS & RECOMMENDATIONS**

### Immediate Actions
1. **Test Payment Flow**: Verify split payment creation and completion
2. **Validate Email Templates**: Ensure all notification emails are working
3. **Check Admin Interface**: Verify all settings are properly saved and displayed
4. **Test Failure Handling**: Simulate payment failures to test retry logic

### Potential Enhancements
1. **Advanced Analytics**: Payment success rates, completion times
2. **Customer Communication**: SMS notifications, in-app alerts
3. **Payment Gateway Integration**: Better failure handling for specific gateways
4. **Reporting**: Split payment performance metrics

### Quality Assurance
1. **Edge Case Testing**: Various payment timing scenarios
2. **Performance Testing**: Large numbers of split payment subscriptions
3. **Security Review**: Payment data handling and validation
4. **User Experience**: Customer-facing interface optimization

## 📚 **RESOURCES**

- **Plugin Documentation**: `docs/` directory
- **Hook Reference**: `hooks.md` for complete hook documentation
- **Code Examples**: See implementation files for usage patterns
- **Support**: Check plugin documentation and support channels

---

*This documentation covers the current implementation as of the latest plugin version. For updates and new features, refer to the plugin changelog and development notes.*