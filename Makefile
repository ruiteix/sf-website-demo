.PHONY: help down up up-ci install test-php test-update clean lint lint-php fixtures-reset
DOCKER_COMPOSE_OVERRIDE ?= dev

ifeq (,$(shell which docker))
EXEC=bash -c
else
DC_OPTS=$(shell [ -t 0 ] || echo '-T')
EXEC=docker-compose exec $(DC_OPTS) --user=www-data php-fpm bash -c
endif

.DEFAULT_GOAL := help

init: docker-compose.$(DOCKER_COMPOSE_OVERRIDE).yml
	cp -f docker-compose.$(DOCKER_COMPOSE_OVERRIDE).yml docker-compose.override.yml
	docker network create demo
	docker login registry.gitlab.com

install: ## run install
	$(EXEC) 'composer install'
	$(EXEC) 'composer dump-autoload'

migrate: ## run doctrine migration migrate
	$(EXEC) 'bin/console doctrine:migrations:migrate'

fixtures-reset: ## Remove database and load fixtures
	$(EXEC) 'bin/console doctrine:d:d --force'
	$(EXEC) 'bin/console doctrine:d:c'
	$(EXEC) 'bin/console doctrine:m:m --no-interaction'
	$(EXEC) 'bin/console doctrine:fixtures:load --no-interaction'

up: ## start containers
	docker-compose pull
	docker-compose up -d --build

down: ## destroy containers
	docker-compose down --remove-orphans --volumes

stop: ## stop containers
	docker-compose stop

restart: ## restart containers
	docker-compose restart

up-ci:
	docker network create ${NETWORK_NAME:-demo}
	docker-compose up -d --build

test: lint ## run unit tests
	$(EXEC) 'bin/console --env=test cache:warmup'
	$(EXEC) 'vendor/bin/simple-phpunit'

sh:
	$(EXEC) zsh

clean:
	docker-compose down --remove-orphans --volumes
	git clean -xdf

lint: ## Dry-run PHP lint
	$(EXEC) 'vendor/bin/php-cs-fixer fix --dry-run --using-cache=no -v --diff --diff-format=udiff'

lint-fix: ## Fix PHP lint
	$(EXEC) 'vendor/bin/php-cs-fixer fix --verbose'

## MISC
help: ## This help dialog.
	@IFS=$$'\n' ; \
	help_lines=(`fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##/:/'`); \
	printf "%-30s %s\n" "Target" "Help" ; \
	printf "%-30s %s\n" "------" "----" ; \
	for help_line in $${help_lines[@]}; do \
		IFS=$$':' ; \
		help_split=($$help_line) ; \
		help_command=`echo $${help_split[0]} | sed -e 's/^ *//' -e 's/ *$$//'` ; \
		help_info=`echo $${help_split[2]} | sed -e 's/^ *//' -e 's/ *$$//'` ; \
		printf '\033[36m'; \
		printf "%-30s %s" $$help_command ; \
		printf '\033[0m'; \
		printf "%s\n" $$help_info; \
	done
