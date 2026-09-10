HOST_UID ?= $(shell id -u)
HOST_GID ?= $(shell id -g)
DOCKER_COMPOSE = HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) docker-compose
PHP = $(DOCKER_COMPOSE) exec -T -u app php
PHP_RUN = $(DOCKER_COMPOSE) run --rm --no-deps -u app php

.PHONY: help build up down restart shell composer install update console cs cs-fix phpstan test test-coverage logs cache-clear permissions bootstrap

help:
	@echo "Available targets:"
	@echo "  make build          - Build Docker images"
	@echo "  make up             - Start containers"
	@echo "  make down           - Stop containers"
	@echo "  make restart        - Restart containers"
	@echo "  make shell          - Open shell in php container"
	@echo "  make install        - composer install"
	@echo "  make update         - composer update"
	@echo "  make composer ARGS= - Run composer with ARGS"
	@echo "  make console ARGS=  - Run bin/console with ARGS"
	@echo "  make cs             - PHP-CS-Fixer dry-run"
	@echo "  make cs-fix        - PHP-CS-Fixer fix"
	@echo "  make phpstan        - Run PHPStan"
	@echo "  make test           - Run PHPUnit"
	@echo "  make test-coverage  - Run PHPUnit with coverage"
	@echo "  make logs           - Follow container logs"
	@echo "  make cache-clear    - Clear Symfony cache"
	@echo "  make permissions    - Fix var/ and vendor/ ownership"
	@echo "  make bootstrap      - Create Symfony project and quality tools"

build:
	$(DOCKER_COMPOSE) build

up:
	-$(DOCKER_COMPOSE) down --remove-orphans
	$(DOCKER_COMPOSE) up -d
	@echo "App: http://localhost:$${HTTP_PORT:-8088}"

down:
	$(DOCKER_COMPOSE) down

restart:
	$(DOCKER_COMPOSE) restart

shell:
	$(DOCKER_COMPOSE) exec -u app php bash

install:
	$(PHP) composer install

update:
	$(PHP) composer update

composer:
	$(PHP) composer $(ARGS)

console:
	$(PHP) php bin/console $(ARGS)

cs:
	$(PHP) vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix:
	$(PHP) vendor/bin/php-cs-fixer fix

phpstan:
	$(PHP) vendor/bin/phpstan analyse

test:
	$(DOCKER_COMPOSE) exec -T -u app -e APP_ENV=test -e XDEBUG_MODE=off php php bin/phpunit

test-coverage:
	$(DOCKER_COMPOSE) exec -T -u app -e APP_ENV=test -e XDEBUG_MODE=coverage php php bin/phpunit --coverage-html var/coverage

logs:
	$(DOCKER_COMPOSE) logs -f --tail=100

cache-clear:
	$(PHP) php bin/console cache:clear

permissions:
	$(DOCKER_COMPOSE) exec -T -u root php sh -c "chown -R app:app /app/var /app/vendor && mkdir -p /app/var/cache /app/var/log /app/var/data"

bootstrap:
	$(DOCKER_COMPOSE) build
	$(DOCKER_COMPOSE) down
	$(DOCKER_COMPOSE) up -d
	$(DOCKER_COMPOSE) exec -T -u root php sh -c "chown -R app:app /app/var /app/vendor"
	$(DOCKER_COMPOSE) run --rm --no-deps -u app php sh -c '\
		if [ ! -f composer.json ]; then \
			composer create-project symfony/skeleton:"7.4.*" /tmp/symfony --no-interaction && \
			cp -a /tmp/symfony/. /app/ && \
			rm -rf /tmp/symfony; \
		fi'
	$(PHP) composer require webapp --no-interaction
	$(PHP) composer require --dev phpunit/phpunit symfony/test-pack friendsofphp/php-cs-fixer phpstan/phpstan phpstan/phpstan-symfony --no-interaction
	$(PHP) php bin/console doctrine:database:create --if-not-exists || true
