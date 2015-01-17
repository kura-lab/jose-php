jose-php
========

Json Web Token (JWT) Library for PHP

### Specification

* http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html

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
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/kura-lab/jose-php"
        }
    ],
    "require": {
        "kura-lab/jose-php": "dev-master"
    }
}
```

Install jose library.

```
$ php composer.phar install
```
