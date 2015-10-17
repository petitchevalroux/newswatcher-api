PHP_SRC_FILES=$(shell find src/ -name '*.php')
PHP_TESTS_FILES=$(shell find tests/ -name '*.php')
PHPUNIT_CMD=./vendor/bin/phpunit

.PHONY: tests
tests: last-tests.lock

last-tests.lock: $(PHP_SRC_FILES) $(PHP_TESTS_FILES) phpunit.xml
	$(PHPUNIT_CMD)
	touch last-tests.lock

.PHONY: coverage
coverage: coverage/index.html

coverage/index.html: $(PHP_SRC_FILES) $(PHP_TESTS_FILES) phpunit.xml
	$(PHPUNIT_CMD) --coverage-html coverage

.PHONY: clean
clean:
	rm -rf last-tests.lock \
	coverage
