CONSOLE = php bin/console
COMPOSER = composer 
VENDOR = vendor/bin

.PHONY: cache-clear and run tests

all:
	make c-clear 
	make tests
	make phpstan
c-clear:
	$(CONSOLE) cache:clear
tests:
	$(VENDOR)/phpunit

phpstan:
	$(VENDOR)/phpstan analyse

