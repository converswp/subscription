<?php
/**
 * Integration Tests for WPSubscription Admin Functionality
 */

use PHPUnit\Framework\TestCase;

class WPS_AdminTest extends TestCase {
    
    protected function setUp(): void {
        parent::setUp();
        
        // Set up admin user
        wp_set_current_user(1);
        
        // Clean up test data
        WPS_TestUtils::cleanup_test_data();
    }
    
    protected function tearDown(): void {
        WPS_TestUtils::cleanup_test_data();
        parent::tearDown();
    }
    
    /**
     * Test admin settings page functionality
     */
    public function test_admin_settings_page() {
        // Simulate admin init
        do_action('admin_init');
        
        // Check if settings are registered
        global $wp_settings_sections;
        $this->assertArrayHasKey('subscrpt_settings', $wp_settings_sections, 'Settings section should be registered');
        
        // Test settings save
        $_POST['subscrpt_settings'] = [
            'enable_subscriptions' => 'yes',
            'default_billing_interval' => 'month',
            'trial_period' => '7'
        ];
        
        $_POST['_wpnonce'] = wp_create_nonce('subscrpt_settings');
        $_POST['action'] = 'update';
        
        // Simulate settings save
        do_action('admin_post_update');
        
        // Check if settings were saved
        $settings = get_option('subscrpt_settings');
        $this->assertNotFalse($settings, 'Settings should be saved');
    }
    
    /**
     * Test subscription management in admin
     */
    public function test_subscription_management() {
        // Create test data
        $user_id = WPS_TestUtils::create_test_user();
        $product_id = WPS_TestUtils::create_test_product();
        $subscription_id = WPS_TestUtils::create_test_subscription([
            'user_id' => $user_id,
            'product_id' => $product_id
        ]);
        
        // Test subscription listing
        $subscriptions = get_posts([
            'post_type' => 'subscrpt_order',
            'post_status' => 'any',
            'numberposts' => -1
        ]);
        
        $this->assertNotEmpty($subscriptions, 'Should have subscriptions in admin');
        
        // Test subscription status update
        $new_status = 'paused';
        update_post_meta($subscription_id, '_subscrpt_status', $new_status);
        
        $updated_subscription = get_post($subscription_id);
        $status = get_post_meta($subscription_id, '_subscrpt_status', true);
        $this->assertEquals($new_status, $status, 'Subscription status should be updated in admin');
    }
    
    /**
     * Test product subscription settings
     */
    public function test_product_subscription_settings() {
        $product_id = WPS_TestUtils::create_test_product([
            'name' => 'Admin Test Product',
            'price' => '49.99',
            'billing_interval' => 'week',
            'trial_period' => 3
        ]);
        
        // Test product meta retrieval
        $product = wc_get_product($product_id);
        $this->assertNotFalse($product, 'Product should be retrievable');
        
        $subscription_enabled = $product->get_meta('_subscrpt_enable');
        $this->assertEquals('yes', $subscription_enabled, 'Subscription should be enabled on product');
        
        $billing_interval = $product->get_meta('_subscrpt_billing_interval');
        $this->assertEquals('week', $billing_interval, 'Billing interval should be weekly');
        
        $trial_period = $product->get_meta('_subscrpt_trial_period');
        $this->assertEquals('3', $trial_period, 'Trial period should be 3 days');
    }
    
    /**
     * Test admin menu structure
     */
    public function test_admin_menu_structure() {
        // Simulate admin menu registration
        do_action('admin_menu');
        
        global $submenu;
        
        // Check if submenu items exist
        $this->assertArrayHasKey('edit.php?post_type=subscrpt_order', $submenu, 'Subscriptions submenu should exist');
        
        $submenu_items = $submenu['edit.php?post_type=subscrpt_order'];
        $this->assertNotEmpty($submenu_items, 'Submenu should have items');
        
        // Check for specific submenu items
        $menu_titles = array_column($submenu_items, 0);
        $this->assertContains('All Subscriptions', $menu_titles, 'All Subscriptions menu should exist');
        $this->assertContains('Add New', $menu_titles, 'Add New menu should exist');
    }
    
    /**
     * Test subscription statistics
     */
    public function test_subscription_statistics() {
        // Create multiple test subscriptions
        $user1 = WPS_TestUtils::create_test_user(['user_login' => 'testuser1']);
        $user2 = WPS_TestUtils::create_test_user(['user_login' => 'testuser2']);
        $product_id = WPS_TestUtils::create_test_product();
        
        // Create active subscription
        WPS_TestUtils::create_test_subscription([
            'user_id' => $user1,
            'product_id' => $product_id,
            'status' => 'active'
        ]);
        
        // Create paused subscription
        WPS_TestUtils::create_test_subscription([
            'user_id' => $user2,
            'product_id' => $product_id,
            'status' => 'paused'
        ]);
        
        // Test subscription count
        $active_subscriptions = get_posts([
            'post_type' => 'subscrpt_order',
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_subscrpt_status',
                    'value' => 'active',
                    'compare' => '='
                ]
            ],
            'numberposts' => -1
        ]);
        
        $this->assertCount(1, $active_subscriptions, 'Should have 1 active subscription');
        
        // Test paused subscriptions
        $paused_subscriptions = get_posts([
            'post_type' => 'subscrpt_order',
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_subscrpt_status',
                    'value' => 'paused',
                    'compare' => '='
                ]
            ],
            'numberposts' => -1
        ]);
        
        $this->assertCount(1, $paused_subscriptions, 'Should have 1 paused subscription');
    }
    
    /**
     * Test admin capabilities
     */
    public function test_admin_capabilities() {
        // Test admin user capabilities
        $admin_user = wp_get_current_user();
        $this->assertTrue($admin_user->has_cap('manage_woocommerce'), 'Admin should have manage_woocommerce capability');
        
        // Test subscription management capabilities
        $this->assertTrue(current_user_can('edit_posts'), 'Admin should be able to edit posts');
        $this->assertTrue(current_user_can('publish_posts'), 'Admin should be able to publish posts');
    }
} 