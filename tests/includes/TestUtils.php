<?php
/**
 * Test Utilities for WPSubscription Plugin
 */

class WPS_TestUtils {
    
    /**
     * Create a test subscription product
     */
    public static function create_test_product($args = []) {
        $defaults = [
            'name' => 'Test Subscription Product',
            'price' => '29.99',
            'billing_interval' => 'month',
            'billing_period' => 1,
            'trial_period' => 7,
            'subscription_limit' => 0
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $product = new WC_Product_Simple();
        $product->set_name($args['name']);
        $product->set_price($args['price']);
        $product->set_regular_price($args['price']);
        $product->set_status('publish');
        
        // Set subscription meta
        $product->update_meta_data('_subscrpt_enable', 'yes');
        $product->update_meta_data('_subscrpt_billing_interval', $args['billing_interval']);
        $product->update_meta_data('_subscrpt_billing_period', $args['billing_period']);
        $product->update_meta_data('_subscrpt_trial_period', $args['trial_period']);
        $product->update_meta_data('_subscrpt_subscription_limit', $args['subscription_limit']);
        
        $product_id = $product->save();
        
        return $product_id;
    }
    
    /**
     * Create a test user
     */
    public static function create_test_user($args = []) {
        $defaults = [
            'user_login' => 'testuser_' . uniqid(),
            'user_email' => 'test_' . uniqid() . '@example.com',
            'user_pass' => 'password123',
            'role' => 'customer'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $user_id = wp_create_user($args['user_login'], $args['user_pass'], $args['user_email']);
        
        if (!is_wp_error($user_id)) {
            $user = new WP_User($user_id);
            $user->set_role($args['role']);
            return $user_id;
        }
        
        return false;
    }
    
    /**
     * Create a test subscription
     */
    public static function create_test_subscription($args = []) {
        $defaults = [
            'user_id' => 1,
            'product_id' => 1,
            'status' => 'active',
            'billing_interval' => 'month',
            'billing_period' => 1,
            'trial_period' => 7,
            'next_payment_date' => date('Y-m-d H:i:s', strtotime('+1 month'))
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $subscription_data = [
            'post_title' => 'Test Subscription',
            'post_type' => 'subscrpt_order',
            'post_status' => 'publish',
            'post_author' => $args['user_id']
        ];
        
        $subscription_id = wp_insert_post($subscription_data);
        
        if (!is_wp_error($subscription_id)) {
            update_post_meta($subscription_id, '_subscrpt_user_id', $args['user_id']);
            update_post_meta($subscription_id, '_subscrpt_product_id', $args['product_id']);
            update_post_meta($subscription_id, '_subscrpt_status', $args['status']);
            update_post_meta($subscription_id, '_subscrpt_billing_interval', $args['billing_interval']);
            update_post_meta($subscription_id, '_subscrpt_billing_period', $args['billing_period']);
            update_post_meta($subscription_id, '_subscrpt_trial_period', $args['trial_period']);
            update_post_meta($subscription_id, '_subscrpt_next_payment_date', $args['next_payment_date']);
            
            return $subscription_id;
        }
        
        return false;
    }
    
    /**
     * Clean up test data
     */
    public static function cleanup_test_data() {
        global $wpdb;
        
        // Delete test products
        $products = get_posts([
            'post_type' => 'product',
            'post_status' => 'any',
            'numberposts' => -1,
            'meta_query' => [
                [
                    'key' => '_subscrpt_enable',
                    'value' => 'yes',
                    'compare' => '='
                ]
            ]
        ]);
        
        foreach ($products as $product) {
            wp_delete_post($product->ID, true);
        }
        
        // Delete test subscriptions
        $subscriptions = get_posts([
            'post_type' => 'subscrpt_order',
            'post_status' => 'any',
            'numberposts' => -1
        ]);
        
        foreach ($subscriptions as $subscription) {
            wp_delete_post($subscription->ID, true);
        }
        
        // Delete test users
        $users = get_users([
            'role' => 'customer',
            'meta_query' => [
                [
                    'key' => 'user_email',
                    'value' => 'test_',
                    'compare' => 'LIKE'
                ]
            ]
        ]);
        
        foreach ($users as $user) {
            wp_delete_user($user->ID);
        }
    }
    
    /**
     * Check if plugin is active
     */
    public static function is_plugin_active() {
        return is_plugin_active('subscription/subscription.php');
    }
    
    /**
     * Check if required plugins are active
     */
    public static function check_dependencies() {
        $dependencies = [
            'woocommerce/woocommerce.php' => 'WooCommerce',
            'subscription/subscription.php' => 'WPSubscription'
        ];
        
        $missing = [];
        
        foreach ($dependencies as $plugin => $name) {
            if (!is_plugin_active($plugin)) {
                $missing[] = $name;
            }
        }
        
        return $missing;
    }
} 