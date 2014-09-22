VERSION = 1.0.0

.title:
	@echo "AwMailer - v$(VERSION)\n"

default: .title
	@echo "See 'make help' for instructions."

help: .title
	@echo "\t   help: Show this message"
	@echo "\t  check: Check the app dependecies"
	@echo "\tinstall: Configure the app environment and install dependencies via composer"
	@echo "\t     db: Initialize and create databases"
	@echo "\t   docs: Generate documentation of API and sourcecode"
	@echo "\t   test: Perform unit tests"
	@echo "\t  clean: Clear custom project files (reset)"
	@echo "\t  sniff: Fix code syntax for commit"
	@echo "\n"

check: .title
	@php .build/dependency-checker.php

install: .title
	@`cd app && mkdir cache && mkdir log && chmod -R 777 cache && chmod -R 777 log && \
	cd config && cp application.ini.sample application.ini && \
	cd ../../web/ && mkdir docs && cd docs && mkdir api && mkdir source && cd ../../ && cp blueprint.apib.sample blueprint.apib && \
	chmod +x bin/awd && chmod +x bin/awmailer`
	@echo "attempting to download composer packager."
	@curl -s http://getcomposer.org/installer | php -- --quiet
	@echo "installing packages..."
	@php composer.phar install -v
	@rm -Rf composer.phar
	@echo "Success!"
	@echo "Please update the configuration files then run 'make db'"

db: .title
	@php .build/database.php

docs: .title
	@echo "generating api documentation..."
	@rm -Rf web/docs/api/*
	@rm -Rf web/docs/source/*
	@php .build/parse-blueprint.php
	@`aglio -t slate -i blueprint.apib -o web/docs/api/index.html > /dev/null 2>&1`
	@echo "generating sourcecode documentation..."
	@`./vendor/bin/phpdoc.php --force > /dev/null 2>&1`
	@rm -rf phpdoc-cache-* > /dev/null 2>&1

test: .title
	@php vendor/bin/phpunit --testdox

clean: .title
	@rm -Rf app/cache
	@rm -Rf app/log
	@rm -Rf app/config/application.ini
	@rm -Rf vendor
	@rm -Rf web/docs
	@rm -Rf blueprint.apib
	@echo "Success!"

sniff: .title
	@cd ./app/; php ../vendor/bin/php-cs-fixer -v fix --level=all --fixers=indentation,linefeed,trailing_spaces,unused_use,return,php_closing_tag,short_tag,visibility,braces,extra_empty_lines,phpdoc_params,eof_ending,include,controls_spaces,elseif .
	@cd ./src/; php ../vendor/bin/php-cs-fixer -v fix --level=all --fixers=indentation,linefeed,trailing_spaces,unused_use,return,php_closing_tag,short_tag,visibility,braces,extra_empty_lines,phpdoc_params,eof_ending,include,controls_spaces,elseif .