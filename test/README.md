# WPSubscription Plugin - Automated Testing

This directory contains automated tests for the WPSubscription plugin using PHPUnit.

## 🚀 Quick Start

### Prerequisites
- PHP 7.0 or higher
- Composer
- WordPress installation
- WooCommerce plugin

### Installation

1. **Install Composer dependencies:**
   ```bash
   cd wp-content/plugins/subscription
   composer install
   ```

2. **Make test runner executable:**
   ```bash
   chmod +x run-tests.sh
   ```

## 🧪 Running Tests

### Using the Test Runner Script

```bash
# Run all tests
./run-tests.sh

# Run only unit tests
./run-tests.sh unit

# Run only integration tests
./run-tests.sh integration

# Run specific test file
./run-tests.sh plugin    # PluginTest.php
./run-tests.sh admin     # AdminTest.php
./run-tests.sh frontend  # FrontendTest.php

# Run with coverage report
./run-tests.sh coverage

# Quick test (unit tests only, stops on failure)
./run-tests.sh quick
```

### Using PHPUnit Directly

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite="Unit Tests"
vendor/bin/phpunit --testsuite="Integration Tests"

# Run specific test file
vendor/bin/phpunit tests/Unit/PluginTest.php

# Run with coverage
vendor/bin/phpunit --coverage-html tests/logs/coverage
```

### Using Composer Scripts

```bash
# Run all tests
composer test

# Run unit tests only
composer test:unit

# Run integration tests only
composer test:integration

# Run with coverage
composer test:coverage
```

## 📁 Test Structure

```
tests/
├── bootstrap.php              # PHPUnit bootstrap file
├── includes/
│   └── TestUtils.php         # Test utilities and helpers
├── Unit/
│   └── PluginTest.php        # Unit tests for core functionality
├── Integration/
│   ├── AdminTest.php         # Admin panel integration tests
│   └── FrontendTest.php      # Frontend integration tests
└── logs/                     # Test logs and coverage reports
    ├── junit.xml
    ├── coverage.txt
    └── coverage/
```

## 🧩 Test Categories

### Unit Tests (`tests/Unit/`)
- Plugin activation/deactivation
- Core functionality
- Helper functions
- Data validation

### Integration Tests (`tests/Integration/`)
- Admin panel functionality
- Frontend user interactions
- Database operations
- WordPress hooks and filters

## 📊 Test Coverage

The tests cover:

- ✅ Plugin activation and dependencies
- ✅ Admin menu registration
- ✅ Custom post type registration
- ✅ Product subscription settings
- ✅ Subscription creation and management
- ✅ Frontend product display
- ✅ Cart and checkout functionality
- ✅ User account subscription management
- ✅ Subscription status changes (pause, cancel, resume)

## 🔧 Test Utilities

The `TestUtils` class provides helper methods:

- `create_test_product()` - Create subscription products
- `create_test_user()` - Create test users
- `create_test_subscription()` - Create test subscriptions
- `cleanup_test_data()` - Clean up test data
- `is_plugin_active()` - Check plugin status
- `check_dependencies()` - Verify required plugins

## 📝 Writing New Tests

### Example Test Method

```php
public function test_new_feature() {
    // Arrange
    $test_data = WPS_TestUtils::create_test_product();
    
    // Act
    $result = some_function($test_data);
    
    // Assert
    $this->assertTrue($result);
    $this->assertEquals('expected', $result);
}
```

### Test Naming Convention

- Test methods should start with `test_`
- Use descriptive names: `test_subscription_creation_with_trial()`
- Group related tests in the same class

## 🐛 Troubleshooting

### Common Issues

1. **WordPress not found:**
   - Ensure you're running tests from `wp-content/plugins/subscription/`
   - Check that WordPress is installed in the expected location

2. **Composer dependencies missing:**
   ```bash
   composer install
   ```

3. **Permission denied:**
   ```bash
   chmod +x run-tests.sh
   ```

4. **PHPUnit not found:**
   ```bash
   composer install --dev
   ```

### Debug Mode

Run tests with verbose output:
```bash
vendor/bin/phpunit --verbose
```

## 📈 Continuous Integration

The test suite is designed to work with CI/CD pipelines:

```yaml
# Example GitHub Actions workflow
- name: Run Tests
  run: |
    cd wp-content/plugins/subscription
    composer install
    ./run-tests.sh
```

## 📋 Test Results

Test results are saved in `tests/logs/`:
- `junit.xml` - JUnit format for CI tools
- `coverage.txt` - Text coverage report
- `coverage/` - HTML coverage report

## 🎯 Testing Goals

- Ensure plugin functionality after security fixes
- Verify no regressions in existing features
- Test all critical user workflows
- Validate data integrity
- Check WordPress compatibility 