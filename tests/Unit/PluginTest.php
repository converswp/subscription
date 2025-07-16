<?php
/**
 * Unit Tests for WPSubscription Plugin
 */

use PHPUnit\Framework\TestCase;

class WPS_PluginTest extends TestCase {
    
    protected function setUp(): void {
        parent::setUp();
        
        // Clean up any existing test data
        WPS_TestUtils::cleanup_test_data();
    }
    
    protected function tearDown(): void {
        // Clean up after each test
        WPS_TestUtils::cleanup_test_data();
        parent::tearDown();
    }
    
    /**
     * Test plugin activation
     */
    public function test_plugin_activation() {
        // Check if plugin is active
        $this->assertTrue(WPS_TestUtils::is_plugin_active());
        
        // Check if required dependencies are active
        $missing_deps = WPS_TestUtils::check_dependencies();
        $this->assertEmpty($missing_deps, 'Missing dependencies: ' . implode(', ', $missing_deps));
    }
    
    /**
     * Test admin menu registration
     */
    public function test_admin_menu_registration() {
        // Skip this test in CLI environment as admin menu is not available
        if (defined('WP_CLI') || php_sapi_name() === 'cli') {
            $this->markTestSkipped('Admin menu not available in CLI environment');
            return;
        }
        
        // Simulate admin init
        do_action('admin_menu');
        
        // Check if menu exists
        global $menu;
        $menu_exists = false;
        
        // Check if menu is available
        if (is_array($menu)) {
            foreach ($menu as $item) {
                if (isset($item[0]) && strpos($item[0], 'Subscriptions') !== false) {
                    $menu_exists = true;
                    break;
                }
            }
        }
        
        $this->assertTrue($menu_exists, 'Subscriptions menu should be registered');
    }
    
    /**
     * Test custom post type registration
     */
    public function test_custom_post_type_registration() {
        $post_types = get_post_types();
        
        $this->assertArrayHasKey('subscrpt_order', $post_types, 'subscrpt_order post type should be registered');
    }
    
    /**
     * Test product meta fields
     */
    public function test_product_meta_fields() {
        $product_id = WPS_TestUtils::create_test_product();
        
        $this->assertNotFalse($product_id, 'Test product should be created');
        
        // Check if subscription meta fields exist
        $enable_subscription = get_post_meta($product_id, '_subscrpt_enable', true);
        $this->assertEquals('yes', $enable_subscription, 'Subscription should be enabled');
        
        $billing_interval = get_post_meta($product_id, '_subscrpt_billing_interval', true);
        $this->assertEquals('month', $billing_interval, 'Billing interval should be set');
    }
    
    /**
     * Test subscription creation
     */
    public function test_subscription_creation() {
        $user_id = WPS_TestUtils::create_test_user();
        $product_id = WPS_TestUtils::create_test_product();
        
        $subscription_id = WPS_TestUtils::create_test_subscription([
            'user_id' => $user_id,
            'product_id' => $product_id
        ]);
        
        $this->assertNotFalse($subscription_id, 'Test subscription should be created');
        
        // Check subscription meta
        $user_meta = get_post_meta($subscription_id, '_subscrpt_user_id', true);
        $this->assertEquals($user_id, $user_meta, 'User ID should be set correctly');
        
        $product_meta = get_post_meta($subscription_id, '_subscrpt_product_id', true);
        $this->assertEquals($product_id, $product_meta, 'Product ID should be set correctly');
    }
    
    /**
     * Test subscription status management
     */
    public function test_subscription_status_management() {
        $subscription_id = WPS_TestUtils::create_test_subscription();
        
        // Test status update
        update_post_meta($subscription_id, '_subscrpt_status', 'paused');
        $status = get_post_meta($subscription_id, '_subscrpt_status', true);
        $this->assertEquals('paused', $status, 'Subscription status should be updated');
        
        // Test status change to cancelled
        update_post_meta($subscription_id, '_subscrpt_status', 'cancelled');
        $status = get_post_meta($subscription_id, '_subscrpt_status', true);
        $this->assertEquals('cancelled', $status, 'Subscription status should be cancelled');
    }
    
    /**
     * Test billing interval validation
     */
    public function test_billing_interval_validation() {
        $valid_intervals = ['day', 'week', 'month', 'year'];
        
        foreach ($valid_intervals as $interval) {
            $product_id = WPS_TestUtils::create_test_product([
                'billing_interval' => $interval
            ]);
            
            $this->assertNotFalse($product_id, "Product with {$interval} interval should be created");
            
            $saved_interval = get_post_meta($product_id, '_subscrpt_billing_interval', true);
            $this->assertEquals($interval, $saved_interval, "Billing interval should be {$interval}");
        }
    }
    
    /**
     * Test trial period functionality
     */
    public function test_trial_period_functionality() {
        $product_id = WPS_TestUtils::create_test_product([
            'trial_period' => 14
        ]);
        
        $trial_period = get_post_meta($product_id, '_subscrpt_trial_period', true);
        $this->assertEquals('14', $trial_period, 'Trial period should be set to 14 days');
        
        $subscription_id = WPS_TestUtils::create_test_subscription([
            'product_id' => $product_id,
            'trial_period' => 14
        ]);
        
        $subscription_trial = get_post_meta($subscription_id, '_subscrpt_trial_period', true);
        $this->assertEquals('14', $subscription_trial, 'Subscription trial period should be set');
    }
} 