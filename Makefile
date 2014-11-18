VERSION = 1.0.1
AW_BIN = $(shell pwd)/bin
DAEMON = $(AW_BIN)/awd
SERVICE = $(AW_BIN)/awmailer
HANDLER = $(AW_BIN)/awmailerctl

.title:
	@echo "AwMailer - v$(VERSION)\n"

default: .title
	@`cd app && mkdir cache && mkdir log && chmod -R 777 cache && chmod -R 777 log && cd log && mkdir processes && \
	chmod -R 777 processes && cd ../ &&  \
	cd config && cp application.ini.sample application.ini && \
	cd ../../web/ && mkdir docs && cd docs && mkdir api && mkdir source && cd ../../ && cp blueprint.md blueprint.apib && \
	mkdir bin`
	@echo "attempting to download composer packager."
	@curl -s http://getcomposer.org/installer | php -- --quiet
	@echo "installing packages..."
	@php composer.phar install -v
	@rm -Rf composer.phar
	@echo "----------------------------------------------------------------------------"
	@echo "compiling..."
	@php .build/compile.php
	@echo "Success!"
	@echo "Please update the configuration files then run 'make db'"

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
	@echo "You may need run this as sudo."
	@echo "Installing awmailer binaries..."
	@`mkdir /var/run/awmailer`
	@`chmod +x $(DAEMON)`
	@`chmod +x $(SERVICE)`
	@`chmod +x $(HANDLER)`
	@`ln -s $(DAEMON) /usr/local/bin/awd`
	@`ln -s $(SERVICE) /usr/local/bin/awmailer`
	@`ln -s $(HANDLER) /etc/init.d/awd`

db: .title
	@php .build/database.php

docs: .title
	@rm -Rf web/docs/api/*
	@rm -Rf web/docs/source/*
	@php .build/parse-docs.php
	@echo "generating api documentation..."
	@`aglio -t slate -i blueprint.apib -o web/docs/api/index.html > /dev/null 2>&1`
	@echo "generating sourcecode documentation..."
	@`./vendor/bin/apigen generate > /dev/null 2>&1`

test: .title
	@php vendor/bin/phpunit --testdox

clean: .title
	@rm -Rf app/cache
	@rm -Rf app/log
	@rm -Rf app/config/application.ini
	@rm -Rf bin
	@rm -Rf vendor
	@rm -Rf web/docs
	@rm -Rf blueprint.apib
	@rm -Rf /usr/local/bin/awd
	@rm -Rf /usr/local/bin/awmailer
	@rm -Rf /etc/init.d/awd
	@rm -Rf /var/run/awmailer
	@echo "Success!"

sniff: .title
	@cd ./app/; php ../vendor/bin/php-cs-fixer -v fix --level=all --fixers=indentation,linefeed,trailing_spaces,unused_use,return,php_closing_tag,short_tag,visibility,braces,extra_empty_lines,phpdoc_params,eof_ending,include,controls_spaces,elseif .
	@cd ./src/; php ../vendor/bin/php-cs-fixer -v fix --level=all --fixers=indentation,linefeed,trailing_spaces,unused_use,return,php_closing_tag,short_tag,visibility,braces,extra_empty_lines,phpdoc_params,eof_ending,include,controls_spaces,elseif .