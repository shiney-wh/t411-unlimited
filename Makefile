all: build-prod

requirements:
	@if ! (type "composer.phar" || type "composer" ) > /dev/null ; then echo "[ERROR] You must install composer\n -> https://getcomposer.org/download/" ; exit ; fi

build-prod: requirements
	@if [ ! -d "vendors" ]; then echo "[ERROR] You must run 'composer install' " ; exit; fi
	SYMFONY_ENV=prod composer install -o --no-dev
