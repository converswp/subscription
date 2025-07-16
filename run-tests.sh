#!/bin/bash

# WPSubscription Plugin Test Runner
# This script runs automated tests for the WPSubscription plugin

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Plugin directory
PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PLUGIN_DIR"

echo -e "${BLUE}WPSubscription Plugin Test Runner${NC}"
echo "=================================="

# Check if we're in the right directory
if [ ! -f "subscription.php" ]; then
    echo -e "${RED}Error: subscription.php not found. Please run this script from the plugin root directory.${NC}"
    exit 1
fi

# Check if WordPress is available
if [ ! -f "../../../wp-config.php" ]; then
    echo -e "${YELLOW}Warning: WordPress not found in expected location.${NC}"
    echo "Make sure you're running this from wp-content/plugins/subscription/"
fi

# Check if Composer dependencies are installed
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}Installing Composer dependencies...${NC}"
    composer install --no-dev
    composer install
fi

# Create logs directory
mkdir -p tests/logs

# Function to run tests
run_tests() {
    local test_type="$1"
    local test_name="$2"
    
    echo -e "${BLUE}Running $test_name...${NC}"
    
    if vendor/bin/phpunit --testsuite="$test_type"; then
        echo -e "${GREEN}✓ $test_name passed${NC}"
        return 0
    else
        echo -e "${RED}✗ $test_name failed${NC}"
        return 1
    fi
}

# Function to run specific test file
run_test_file() {
    local test_file="$1"
    
    echo -e "${BLUE}Running test file: $test_file${NC}"
    
    if vendor/bin/phpunit "$test_file"; then
        echo -e "${GREEN}✓ Test file passed${NC}"
        return 0
    else
        echo -e "${RED}✗ Test file failed${NC}"
        return 1
    fi
}

# Main test execution
case "${1:-all}" in
    "unit")
        run_tests "Unit Tests" "Unit Tests"
        ;;
    "integration")
        run_tests "Integration Tests" "Integration Tests"
        ;;
    "plugin")
        run_test_file "tests/Unit/PluginTest.php"
        ;;
    "admin")
        run_test_file "tests/Integration/AdminTest.php"
        ;;
    "frontend")
        run_test_file "tests/Integration/FrontendTest.php"
        ;;
    "coverage")
        echo -e "${BLUE}Running tests with coverage...${NC}"
        vendor/bin/phpunit --coverage-html tests/logs/coverage --coverage-text=tests/logs/coverage.txt
        echo -e "${GREEN}Coverage report generated in tests/logs/coverage/${NC}"
        ;;
    "quick")
        echo -e "${BLUE}Running quick test suite...${NC}"
        vendor/bin/phpunit --testsuite="Unit Tests" --stop-on-failure
        ;;
    "all"|*)
        echo -e "${BLUE}Running all tests...${NC}"
        
        # Run unit tests
        if run_tests "Unit Tests" "Unit Tests"; then
            unit_passed=true
        else
            unit_passed=false
        fi
        
        # Run integration tests
        if run_tests "Integration Tests" "Integration Tests"; then
            integration_passed=true
        else
            integration_passed=false
        fi
        
        # Summary
        echo ""
        echo -e "${BLUE}Test Summary:${NC}"
        echo "=============="
        
        if [ "$unit_passed" = true ]; then
            echo -e "${GREEN}✓ Unit Tests: PASSED${NC}"
        else
            echo -e "${RED}✗ Unit Tests: FAILED${NC}"
        fi
        
        if [ "$integration_passed" = true ]; then
            echo -e "${GREEN}✓ Integration Tests: PASSED${NC}"
        else
            echo -e "${RED}✗ Integration Tests: FAILED${NC}"
        fi
        
        if [ "$unit_passed" = true ] && [ "$integration_passed" = true ]; then
            echo -e "${GREEN}🎉 All tests passed!${NC}"
            exit 0
        else
            echo -e "${RED}❌ Some tests failed. Please check the output above.${NC}"
            exit 1
        fi
        ;;
esac

echo ""
echo -e "${BLUE}Test execution completed.${NC}"
echo "Check tests/logs/ for detailed reports." 