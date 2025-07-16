<?php
/**
 * Integration Tests for WPSubscription Frontend Functionality
 */

use PHPUnit\Framework\TestCase;

class WPS_FrontendTest extends TestCase {
    
    protected function setUp(): void {
        parent::setUp();
        
        // Set up customer user
        $customer_id = WPS_TestUtils::create_test_user([
            'role' => 'customer'
        ]);
        wp_set_current_user($customer_id);
        
        // Clean up test data
        WPS_TestUtils::cleanup_test_data();
    }
    
    protected function tearDown(): void {
        WPS_TestUtils::cleanup_test_data();
        parent::tearDown();
    }
    
    /**
     * Test subscription product display on frontend
     */
    public function test_subscription_product_display() {
        $product_id = WPS_TestUtils::create_test_product([
            'name' => 'Frontend Test Product',
            'price' => '19.99',
            'billing_interval' => 'month',
            'trial_period' => 7
        ]);
        
        $product = wc_get_product($product_id);
        
        // Test product availability
        $this->assertTrue($product->is_visible(), 'Product should be visible on frontend');
        $this->assertTrue($product->is_purchasable(), 'Product should be purchasable');
        
        // Test subscription meta display
        $subscription_enabled = $product->get_meta('_subscrpt_enable');
        $this->assertEquals('yes', $subscription_enabled, 'Subscription should be enabled');
        
        // Test price display
        $price = $product->get_price();
        $this->assertEquals('19.99', $price, 'Product price should be displayed correctly');
    }
    
    /**
     * Test cart functionality with subscription products
     */
    public function test_cart_functionality() {
        $product_id = WPS_TestUtils::create_test_product();
        
        // Add product to cart
        $cart_item_key = WC()->cart->add_to_cart($product_id, 1);
        
        $this->assertNotFalse($cart_item_key, 'Product should be added to cart');
        
        // Check cart contents
        $cart_items = WC()->cart->get_cart();
        $this->assertNotEmpty($cart_items, 'Cart should contain items');
        
        // Check if subscription info is in cart
        $cart_item = $cart_items[$cart_item_key];
        $this->assertEquals($product_id, $cart_item['product_id'], 'Correct product should be in cart');
        
        // Test cart total
        $cart_total = WC()->cart->get_total();
        $this->assertNotEmpty($cart_total, 'Cart should have a total');
    }
    
    /**
     * Test checkout process with subscription
     */
    public function test_checkout_process() {
        $product_id = WPS_TestUtils::create_test_product();
        
        // Add product to cart
        WC()->cart->add_to_cart($product_id, 1);
        
        // Set up checkout data
        $checkout_data = [
            'billing_first_name' => 'Test',
            'billing_last_name' => 'Customer',
            'billing_email' => 'test@example.com',
            'billing_phone' => '1234567890',
            'billing_address_1' => '123 Test St',
            'billing_city' => 'Test City',
            'billing_state' => 'CA',
            'billing_postcode' => '12345',
            'billing_country' => 'US',
            'payment_method' => 'stripe',
            'terms' => '1'
        ];
        
        // Test checkout validation
        $validation = WC()->checkout()->is_valid();
        $this->assertTrue($validation, 'Checkout should be valid');
        
        // Test order creation
        $order_id = WC()->checkout()->create_order($checkout_data);
        
        if (!is_wp_error($order_id)) {
            $order = wc_get_order($order_id);
            $this->assertNotFalse($order, 'Order should be created');
            
            // Check if order has subscription items
            $has_subscription_items = false;
            foreach ($order->get_items() as $item) {
                $product = $item->get_product();
                if ($product && $product->get_meta('_subscrpt_enable') === 'yes') {
                    $has_subscription_items = true;
                    break;
                }
            }
            
            $this->assertTrue($has_subscription_items, 'Order should contain subscription items');
        }
    }
    
    /**
     * Test my account subscription display
     */
    public function test_my_account_subscriptions() {
        $user_id = get_current_user_id();
        $product_id = WPS_TestUtils::create_test_product();
        
        // Create subscription for current user
        $subscription_id = WPS_TestUtils::create_test_subscription([
            'user_id' => $user_id,
            'product_id' => $product_id,
            'status' => 'active'
        ]);
        
        // Test subscription retrieval for user
        $user_subscriptions = get_posts([
            'post_type' => 'subscrpt_order',
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_subscrpt_user_id',
                    'value' => $user_id,
                    'compare' => '='
                ]
            ],
            'numberposts' => -1
        ]);
        
        $this->assertNotEmpty($user_subscriptions, 'User should have subscriptions');
        $this->assertCount(1, $user_subscriptions, 'User should have 1 subscription');
        
        // Test subscription details
        $subscription = $user_subscriptions[0];
        $status = get_post_meta($subscription->ID, '_subscrpt_status', true);
        $this->assertEquals('active', $status, 'Subscription should be active');
    }
    
    /**
     * Test subscription cancellation
     */
    public function test_subscription_cancellation() {
        $user_id = get_current_user_id();
        $product_id = WPS_TestUtils::create_test_product();
        
        $subscription_id = WPS_TestUtils::create_test_subscription([
            'user_id' => $user_id,
            'product_id' => $product_id,
            'status' => 'active'
        ]);
        
        // Test cancellation
        update_post_meta($subscription_id, '_subscrpt_status', 'cancelled');
        
        $status = get_post_meta($subscription_id, '_subscrpt_status', true);
        $this->assertEquals('cancelled', $status, 'Subscription should be cancelled');
        
        // Test user can see cancelled subscription
        $cancelled_subscriptions = get_posts([
            'post_type' => 'subscrpt_order',
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_subscrpt_user_id',
                    'value' => $user_id,
                    'compare' => '='
                ],
                [
                    'key' => '_subscrpt_status',
                    'value' => 'cancelled',
                    'compare' => '='
                ]
            ],
            'numberposts' => -1
        ]);
        
        $this->assertCount(1, $cancelled_subscriptions, 'User should see cancelled subscription');
    }
    
    /**
     * Test subscription pause/resume
     */
    public function test_subscription_pause_resume() {
        $user_id = get_current_user_id();
        $product_id = WPS_TestUtils::create_test_product();
        
        $subscription_id = WPS_TestUtils::create_test_subscription([
            'user_id' => $user_id,
            'product_id' => $product_id,
            'status' => 'active'
        ]);
        
        // Test pause
        update_post_meta($subscription_id, '_subscrpt_status', 'paused');
        $status = get_post_meta($subscription_id, '_subscrpt_status', true);
        $this->assertEquals('paused', $status, 'Subscription should be paused');
        
        // Test resume
        update_post_meta($subscription_id, '_subscrpt_status', 'active');
        $status = get_post_meta($subscription_id, '_subscrpt_status', true);
        $this->assertEquals('active', $status, 'Subscription should be resumed');
    }
} 