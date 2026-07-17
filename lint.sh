#!/usr/bin/env bash

# lint.sh - Code quality and style checking for the Cars & Inspections API
# Usage: ./lint.sh [--fix]

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

check_docker() {
    if ! docker exec cars_app true &> /dev/null; then
        echo -e "${RED}❌ Container 'cars_app' is not running. Run ./setup.sh first.${NC}"
        exit 1
    fi
}

main() {
    check_docker
    
    if [ "${1:-}" = "--fix" ]; then
        echo -e "${BLUE}🔧 Running PHP CS Fixer...${NC}"
        docker exec cars_app ./vendor/bin/php-cs-fixer fix
        echo -e "${GREEN}✅ Code fixed!${NC}"
    else
        echo -e "${BLUE}🔍 Running PHP CS Fixer (dry-run)...${NC}"
        docker exec cars_app ./vendor/bin/php-cs-fixer fix --dry-run --diff
        echo ""
        echo -e "${YELLOW}To apply fixes, run: ./lint.sh --fix${NC}"
    fi
}

main "$@"
