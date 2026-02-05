#!/bin/bash

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Options
SKIP_SAMPLE_DATA=${SKIP_SAMPLE_DATA:-false}  # Set to true to skip sample gallery creation

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# Function to wait for WordPress to be ready
wait_for_wordpress() {
    echo -e "${YELLOW}Waiting for WordPress to be ready...${NC}"
    local max_attempts=60
    local attempt=1
    while [ $attempt -le $max_attempts ]; do
        if curl -s "http://localhost:$WP_PORT" > /dev/null 2>&1; then
            echo -e "${GREEN}✓ WordPress is ready${NC}"
            return 0
        fi
        sleep 2
        attempt=$((attempt + 1))
    done
    echo -e "${RED}✗ WordPress did not become ready in time${NC}"
    return 1
}

# Function to install WordPress via WP-CLI
install_wordpress() {
    echo ""
    echo -e "${CYAN}Installing WordPress...${NC}"

    # Install WP-CLI if not present (must be done before any wp commands)
    docker exec zul_gallery_wp bash -c "
        if ! command -v wp &> /dev/null; then
            echo 'Installing WP-CLI...'
            curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
            chmod +x wp-cli.phar
            mv wp-cli.phar /usr/local/bin/wp
        fi
    "

    # Check if WordPress is already installed
    if docker exec zul_gallery_wp wp core is-installed --path=/var/www/html --allow-root 2>/dev/null; then
        echo -e "${GREEN}✓ WordPress is already installed${NC}"
        return 0
    fi

    # Install WordPress
    docker exec zul_gallery_wp wp core install \
        --path=/var/www/html \
        --url="http://localhost:$WP_PORT" \
        --title="ZUL Gallery Dev Site" \
        --admin_user=admin \
        --admin_password=admin \
        --admin_email=admin@example.com \
        --skip-email \
        --allow-root

    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ WordPress installed successfully${NC}"

        # Activate the plugin
        echo -e "${CYAN}Activating ZUL Gallery plugin...${NC}"
        docker exec zul_gallery_wp wp plugin activate zul-gallery-plugin --path=/var/www/html --allow-root
        echo -e "${GREEN}✓ Plugin activated${NC}"

        return 0
    else
        echo -e "${RED}✗ WordPress installation failed${NC}"
        return 1
    fi
}

# Function to create sample galleries
create_sample_galleries() {
    if [ "$SKIP_SAMPLE_DATA" = "true" ]; then
        echo -e "${YELLOW}Skipping sample data creation (SKIP_SAMPLE_DATA=true)${NC}"
        return 0
    fi

    echo ""
    echo -e "${CYAN}Creating sample galleries...${NC}"

    # Check if galleries already exist
    local gallery_count=$(docker exec zul_gallery_wp wp db query "SELECT COUNT(*) FROM wp_zul_image_gallery" --path=/var/www/html --allow-root 2>/dev/null | tail -1)
    if [ "$gallery_count" != "" ] && [ "$gallery_count" -gt 0 ] 2>/dev/null; then
        echo -e "${YELLOW}Sample galleries already exist (found $gallery_count galleries)${NC}"
        return 0
    fi

    # Run the sample data creation script
    docker exec zul_gallery_wp php /var/www/html/wp-content/plugins/zul-gallery-plugin/scripts/create-sample-data.php

    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Sample galleries created${NC}"
        return 0
    else
        echo -e "${RED}✗ Failed to create sample galleries${NC}"
        return 1
    fi
}

# Function to display final URLs
display_urls() {
    echo ""
    echo -e "${BOLD}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BOLD}${GREEN}                    ZUL Gallery Plugin - Ready!                          ${NC}"
    echo -e "${BOLD}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    echo -e "  ${BOLD}${CYAN}WordPress Site:${NC}"
    echo -e "  ${BLUE}➜${NC}  http://localhost:$WP_PORT"
    echo ""
    echo -e "  ${BOLD}${CYAN}Admin Dashboard:${NC}"
    echo -e "  ${BLUE}➜${NC}  http://localhost:$WP_PORT/wp-admin"
    echo -e "      Username: ${BOLD}admin${NC}  |  Password: ${BOLD}admin${NC}"
    echo ""
    echo -e "  ${BOLD}${CYAN}Gallery Admin:${NC}"
    echo -e "  ${BLUE}➜${NC}  http://localhost:$WP_PORT/wp-admin/admin.php?page=zul-galleries"
    echo ""

    # Read and display sample gallery pages
    if [ -f /tmp/zul_gallery_pages.json ] || docker exec zul_gallery_wp test -f /tmp/zul_gallery_pages.json 2>/dev/null; then
        echo -e "  ${BOLD}${CYAN}Sample Gallery Pages:${NC}"

        # Get page info from container
        local pages_json=$(docker exec zul_gallery_wp cat /tmp/zul_gallery_pages.json 2>/dev/null)

        if [ -n "$pages_json" ] && [ "$pages_json" != "[]" ]; then
            echo "$pages_json" | docker exec -i zul_gallery_wp php -r "
\$pages = json_decode(file_get_contents('php://stdin'), true);
if (\$pages) {
    foreach (\$pages as \$i => \$page) {
        \$num = \$i + 1;
        echo \"  ➜  [\$num] {\$page['title']}\n\";
        echo \"      http://localhost:$WP_PORT/?page_id={\$page['id']}\n\";
    }
}
"
        fi
        echo ""
    fi

    echo -e "  ${BOLD}${CYAN}Database:${NC}"
    echo -e "  ${BLUE}➜${NC}  localhost:$DB_PORT"
    echo -e "      Database: ${BOLD}wordpress${NC}  |  User: ${BOLD}wordpress${NC}  |  Pass: ${BOLD}wordpress${NC}"
    echo ""
    echo -e "${BOLD}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    echo -e "  ${BOLD}Commands:${NC}"
    echo -e "  make down       ${CYAN}# Stop containers${NC}"
    echo -e "  make logs       ${CYAN}# View WordPress logs${NC}"
    echo -e "  make test       ${CYAN}# Run PHPUnit tests${NC}"
    echo -e "  make shell      ${CYAN}# Shell into container${NC}"
    echo ""
    echo -e "${BOLD}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
}

# ============================================================================
# MAIN SCRIPT
# ============================================================================

echo ""
echo -e "${BOLD}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BOLD} ZUL Gallery Plugin - Docker Environment${NC}"
echo -e "${BOLD}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Initialize git submodules if needed
if [ ! -f tools/zul-check-ports/check-ports ]; then
    echo "Initializing git submodules..."
    git submodule update --init --recursive
    echo ""
fi

# Check port availability and generate .env.ports
echo "Checking port availability..."
if ! ./docker/scripts/check-ports.sh; then
    echo ""
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Source the generated ports file
if [ -f .env.ports ]; then
    source .env.ports
    export WP_PORT
    export DB_PORT
fi

echo ""

echo -e "${GREEN}Starting Docker containers...${NC}"
echo ""

# Run docker compose
docker compose up -d wordpress

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✓ Containers started${NC}"

    # Wait for WordPress to be ready
    wait_for_wordpress

    if [ $? -eq 0 ]; then
        # Install WordPress
        install_wordpress

        # Create sample galleries
        create_sample_galleries

        # Display final URLs
        display_urls
    fi
else
    echo -e "${RED}Failed to start containers${NC}"
    exit 1
fi
