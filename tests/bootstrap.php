<?php
/**
 * PHPUnit Bootstrap for WPSubscription Plugin Tests
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(dirname(__DIR__)))) . '/');
}

// Load WordPress
require_once ABSPATH . 'wp-config.php';
require_once ABSPATH . 'wp-load.php';

// Load plugin
require_once dirname(__DIR__) . '/subscription.php';

// Set up test environment
define('WP_TESTS_DIR', getenv('WP_TESTS_DIR') ?: '/tmp/wordpress-tests-lib');
define('WP_TESTS_CONFIG', getenv('WP_TESTS_CONFIG') ?: '/tmp/wp-tests-config.php');

// Load PHPUnit
if (!class_exists('PHPUnit\Framework\TestCase')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// Test utilities
require_once __DIR__ . '/includes/TestUtils.php'; 