jose-php
========

Json Web Token (JWT) Library for PHP

### Specification

* http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html

### Requirements

* PHP 5.3.3 or higher.

### Install

At first, install composer.

```
$ mkdir workspace
$ cd workspace
$ curl -s http://getcomposer.org/installer | php
```

Create composer.json.

```
{
    "require": {
        "kura-lab/jose-php": "1.*"
    }
}
```

Install jose library.

```
$ php composer.phar install
```

### Development

Check coding style with CodeSniffer.

```
$ vendor/bin/phpcs --standard=PSR2 src/
```

Execute unit test with PHPUnit.

```
$ vendor/bin/phpunit
```

Fix source code with PHP Coding Standards Fixer.

```
$ vendor/bin/php-cs-fixer fix --config-file .php_cs --verbose --diff --dry-run
$ vendor/bin/php-cs-fixer fix --config-file .php_cs --verbose --diff
```
