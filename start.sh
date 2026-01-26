#!/bin/bash

# Default ports (can be overridden with environment variables)
DB_PORT=${DB_PORT:-3307}
WP_PORT=${WP_PORT:-8080}

# Options
AUTO_PORT=${AUTO_PORT:-false}  # Set to true to auto-select available ports
SKIP_SAMPLE_DATA=${SKIP_SAMPLE_DATA:-false}  # Set to true to skip sample gallery creation

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# Function to find an available port starting from a given port
find_available_port() {
    local start_port=$1
    local port=$start_port
    while lsof -i :$port > /dev/null 2>&1; do
        port=$((port + 1))
        if [ $port -gt $((start_port + 100)) ]; then
            echo "0"
            return
        fi
    done
    echo $port
}

# Function to check if a port is in use
check_port() {
    local port=$1
    local service=$2

    if lsof -i :$port > /dev/null 2>&1; then
        echo -e "${RED}✗ Port $port ($service) is in use${NC}"
        echo "  Used by: $(lsof -i :$port | tail -1 | awk '{print $1, $2}')"
        return 1
    else
        echo -e "${GREEN}✓ Port $port ($service) is available${NC}"
        return 0
    fi
}

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

    # Check if WordPress is already installed
    if docker exec zul_gallery_wp wp core is-installed --path=/var/www/html 2>/dev/null; then
        echo -e "${GREEN}✓ WordPress is already installed${NC}"
        return 0
    fi

    # Install WP-CLI if not present
    docker exec zul_gallery_wp bash -c "
        if ! command -v wp &> /dev/null; then
            curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
            chmod +x wp-cli.phar
            mv wp-cli.phar /usr/local/bin/wp
        fi
    "

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
echo "Checking port availability..."
echo ""

# Check both ports
db_available=true
wp_available=true

check_port $DB_PORT "MySQL" || db_available=false
check_port $WP_PORT "WordPress" || wp_available=false

echo ""

# Handle unavailable ports
if [ "$db_available" = false ] || [ "$wp_available" = false ]; then

    # Auto mode or interactive mode
    if [ "$AUTO_PORT" = "true" ] || [ ! -t 0 ]; then
        echo -e "${YELLOW}Auto-selecting available ports...${NC}"

        if [ "$db_available" = false ]; then
            DB_PORT=$(find_available_port $DB_PORT)
            if [ "$DB_PORT" = "0" ]; then
                echo -e "${RED}Could not find available port for MySQL. Exiting.${NC}"
                exit 1
            fi
        fi

        if [ "$wp_available" = false ]; then
            WP_PORT=$(find_available_port $WP_PORT)
            if [ "$WP_PORT" = "0" ]; then
                echo -e "${RED}Could not find available port for WordPress. Exiting.${NC}"
                exit 1
            fi
        fi

        echo -e "${GREEN}Using ports: MySQL=$DB_PORT, WordPress=$WP_PORT${NC}"
        echo ""
    else
        # Interactive mode
        echo -e "${YELLOW}Some ports are in use. Options:${NC}"
        echo ""
        echo "  1) Auto-select available ports"
        echo "  2) Enter custom ports"
        echo "  3) Exit"
        echo ""
        read -p "Choose option [1-3]: " choice

        case $choice in
            1)
                if [ "$db_available" = false ]; then
                    DB_PORT=$(find_available_port $DB_PORT)
                fi
                if [ "$wp_available" = false ]; then
                    WP_PORT=$(find_available_port $WP_PORT)
                fi
                echo -e "${GREEN}Using ports: MySQL=$DB_PORT, WordPress=$WP_PORT${NC}"
                ;;
            2)
                read -p "Enter MySQL port [default: 3307]: " custom_db
                read -p "Enter WordPress port [default: 8080]: " custom_wp
                DB_PORT=${custom_db:-3307}
                WP_PORT=${custom_wp:-8080}
                ;;
            *)
                echo "Exiting."
                exit 0
                ;;
        esac
        echo ""
    fi
fi

# Export for docker-compose
export DB_PORT
export WP_PORT

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
