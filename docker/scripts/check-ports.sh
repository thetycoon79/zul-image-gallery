#!/bin/bash

# Port checker for zul-gallery-plugin - wrapper around zul-check-ports
# This script uses the standalone port checker with project-specific configuration

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")"

# Use check-ports from tools submodule
"$PROJECT_DIR/tools/zul-check-ports/check-ports" \
    --output "$PROJECT_DIR/.env.ports" \
    --format env \
    WP:8080:8080:8199 \
    DB:3307:3307:3399

# Display access URLs
echo ""
echo -e "\033[1;33mAccess URLs after starting:\033[0m"

# Source the generated file to get the port values
source "$PROJECT_DIR/.env.ports"

echo "  WordPress:   http://localhost:$WP_PORT"
echo "  Admin:       http://localhost:$WP_PORT/wp-admin (admin/admin)"
echo "  MySQL:       localhost:$DB_PORT"
