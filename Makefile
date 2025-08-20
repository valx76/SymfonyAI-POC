DOCKER_COMP = docker compose
PHP_CONT = $(DOCKER_COMP) exec php


.PHONY: php-cs-fixer
php-cs-fixer:
	@$(PHP_CONT) ./vendor/bin/php-cs-fixer check src -v

.PHONY: phpstan
phpstan:
	@$(PHP_CONT) ./vendor/bin/phpstan analyse src

.PHONY: init
init:
	@$(PHP_CONT) curl -X 'POST' \
                   'http://chroma:8000/api/v2/reset' \
                   -H 'accept: application/json' \
                   -d '' \
                   -o /dev/null \
                   --insecure \
                   --silent
	@$(PHP_CONT) php bin/console doctrine:schema:drop --quiet --full-database --force
	@$(PHP_CONT) php bin/console doctrine:migrations:migrate --quiet --no-interaction
	@$(PHP_CONT) php bin/console doctrine:fixtures:load --quiet --no-interaction
	@$(PHP_CONT) php bin/console app:embed --quiet --no-interaction

# Ref: https://stackoverflow.com/questions/6273608/how-to-pass-argument-to-makefile-from-command-line#comment40273073_6273809
%:
	@:
