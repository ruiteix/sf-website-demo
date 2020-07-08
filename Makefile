.PHONY: help down up install test-php phpmd clean lint lint-fix fixtures-reset
DOCKER_COMPOSE_OVERRIDE ?= dev
DOCKER_COMPOSER_USER ?= www-data

ifeq (,$(shell which docker))
EXEC=bash -c
else
DC_OPTS=$(shell [ -t 0 ] || echo '-T')
EXEC=docker-compose exec $(DC_OPTS) --user=$(DOCKER_COMPOSER_USER) php-fpm bash -c
endif

.DEFAULT_GOAL := help

docker-compose.override.yml: docker-compose.$(DOCKER_COMPOSE_OVERRIDE).yml
	cp -f docker-compose.$(DOCKER_COMPOSE_OVERRIDE).yml docker-compose.override.yml

init-docker:
	docker network create demo
	docker login registry.gitlab.com -u $(GITLAB_LOGIN) -p $(GITLAB_PASSWORD)

init: docker-compose.override.yml init-docker

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

phpmd:
	$(EXEC) 'vendor/bin/phpmd src/ text codesize.xml'

test: ## run unit tests
	$(EXEC) 'bin/console --env=test cache:warmup'
	$(EXEC) 'vendor/bin/simple-phpunit --coverage-clover ./.build/clover.xml'

test-coverage-html: ## run unit tests
	$(EXEC) 'vendor/bin/simple-phpunit --coverage-html ./.build/coverage/'

sh:
	$(EXEC) zsh

clean:
	docker-compose down --remove-orphans --volumes
	git clean -xdf

lint: ## Dry-run PHP lint
	$(EXEC) 'php-cs-fixer fix --dry-run --using-cache=no -v --diff --diff-format=udiff'

lint-fix: ## Fix PHP lint
	$(EXEC) 'php-cs-fixer fix --verbose'

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
