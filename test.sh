#!/usr/bin/env bash

# test.sh - Run tests for the Cars & Inspections API
# Usage: ./test.sh [options]
#   ./test.sh                    # Run full test suite
#   ./test.sh --coverage         # Run with coverage report
#   ./test.sh --file <path>      # Run specific test file
#   ./test.sh --filter <name>    # Run tests matching filter
#   ./test.sh --local            # Run tests locally (requires PHP 8.4+)

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
usage() {
    cat <<EOF
${BLUE}Usage: ./test.sh [options]${NC}

${YELLOW}Options:${NC}
  (no args)           Run full test suite in Docker
  --coverage          Run tests with coverage report (HTML output to coverage/)
  --file <path>       Run specific test file (e.g., tests/Feature/AuthApiTest.php)
  --filter <name>     Run tests matching filter (e.g., "CarApiTest")
  --local             Run tests locally (requires PHP 8.4+, no Docker)
  --watch             Run tests and watch for file changes (local only)
  --help              Show this help message

${YELLOW}Examples:${NC}
  ./test.sh                                    # Full suite
  ./test.sh --coverage                         # With coverage
  ./test.sh --file tests/Feature/CarApiTest.php
  ./test.sh --filter "AuthApiTest"
  ./test.sh --local                            # No Docker needed

EOF
    exit 0
}

# Check if Docker is running (for Docker commands)
check_docker() {
    if ! command -v docker &> /dev/null; then
        echo -e "${RED}❌ Docker is not installed${NC}"
        exit 1
    fi

    if ! docker exec cars_app true &> /dev/null; then
        echo -e "${RED}❌ Container 'cars_app' is not running. Run ./setup.sh first.${NC}"
        exit 1
    fi
}

# Run full test suite
run_full_tests() {
    check_docker
    echo -e "${BLUE}🧪 Running full test suite...${NC}"
    echo ""
    docker exec cars_app php artisan test
}

# Run tests with coverage
run_coverage() {
    check_docker
    echo -e "${BLUE}📊 Running tests with coverage report...${NC}"
    echo ""
    docker exec cars_app php artisan test --coverage-html=coverage/
    echo ""
    echo -e "${GREEN}✅ Coverage report generated at: coverage/index.html${NC}"
}

# Run specific test file
run_file_tests() {
    local file="$1"
    check_docker
    
    if [ ! -f "$file" ]; then
        echo -e "${RED}❌ Test file not found: $file${NC}"
        exit 1
    fi
    
    echo -e "${BLUE}🧪 Running tests from: $file${NC}"
    echo ""
    docker exec cars_app php artisan test "$file"
}

# Run tests with filter
run_filter_tests() {
    local filter="$1"
    check_docker
    echo -e "${BLUE}🧪 Running tests matching: $filter${NC}"
    echo ""
    docker exec cars_app php artisan test --filter "$filter"
}

# Run tests locally
run_local_tests() {
    if ! command -v php &> /dev/null; then
        echo -e "${RED}❌ PHP is not installed${NC}"
        exit 1
    fi
    
    php_version=$(php -r "echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;")
    echo -e "${BLUE}🧪 Running tests locally (PHP $php_version)...${NC}"
    echo ""
    
    if [ ! -f "vendor/bin/phpunit" ]; then
        echo -e "${YELLOW}📦 Installing Composer dependencies...${NC}"
        composer install
        echo ""
    fi
    
    php artisan test
}

# Watch tests (requires entr or fswatch)
run_watch_tests() {
    if ! command -v php &> /dev/null; then
        echo -e "${RED}❌ PHP is not installed${NC}"
        exit 1
    fi
    
    if ! command -v entr &> /dev/null && ! command -v fswatch &> /dev/null; then
        echo -e "${RED}❌ File watcher not found. Install 'entr' or 'fswatch' first.${NC}"
        echo -e "${YELLOW}   On macOS: brew install entr${NC}"
        echo -e "${YELLOW}   On Linux: apt-get install entr${NC}"
        exit 1
    fi
    
    echo -e "${BLUE}👀 Watching tests... (Ctrl+C to stop)${NC}"
    echo ""
    
    if command -v entr &> /dev/null; then
        find tests -name "*.php" | entr -c php artisan test
    else
        fswatch -o tests | xargs -I {} php artisan test
    fi
}

# Main
main() {
    case "${1:-}" in
        "")
            run_full_tests
            ;;
        --coverage)
            run_coverage
            ;;
        --file)
            if [ -z "$2" ]; then
                echo -e "${RED}❌ --file requires a path argument${NC}"
                exit 1
            fi
            run_file_tests "$2"
            ;;
        --filter)
            if [ -z "$2" ]; then
                echo -e "${RED}❌ --filter requires a filter argument${NC}"
                exit 1
            fi
            run_filter_tests "$2"
            ;;
        --local)
            run_local_tests
            ;;
        --watch)
            run_watch_tests
            ;;
        --help)
            usage
            ;;
        *)
            echo -e "${RED}❌ Unknown option: $1${NC}"
            usage
            ;;
    esac
}

main "$@"
