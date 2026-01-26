.PHONY: up down test install logs shell clean start

# Start WordPress and MySQL (with port check)
start:
	./start.sh

# Start WordPress and MySQL (skip port check)
up:
	docker compose up -d wordpress

# Stop all containers
down:
	docker compose down

# Install composer dependencies
install:
	docker compose run --rm composer

# Run PHPUnit tests
test:
	docker compose run --rm phpunit

# Quick test (if vendor already exists)
test-quick:
	docker compose run --rm --entrypoint "./vendor/bin/phpunit" phpunit

# View WordPress logs
logs:
	docker compose logs -f wordpress

# Shell into WordPress container
shell:
	docker exec -it zul_gallery_wp bash

# Clean up volumes
clean:
	docker compose down -v
	rm -rf vendor .phpunit.cache coverage

# Full setup and test
all: install test
