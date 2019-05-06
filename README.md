# PHP IMDB Grabber

[![Build Status](https://travis-ci.org/kingio/PHP-IMDB-Grabber.svg?branch=master)](https://travis-ci.org/kingio/PHP-IMDB-Grabber)

This PHP library enables you to scrap data from IMDB.com and it's heavily based on 
[FabianBeiner's PHP-IMDB-Grabber](https://github.com/FabianBeiner/PHP-IMDB-Grabber) 
(You can also find more info about this package, its license and conditions of usage there).
It's begin used on production servers.

# Install

`composer require kingio/php-imdb-grabber`

# Usage

```php
<?php


require './vendor/autoload.php';

$imdbId = "https://www.imdb.com/title/tt0241527/";

$imdb = new \IMDB\IMDB($imdbId);

print_r($imdb->getTitle());
```

# Testing

See on [Travis CI](https://travis-ci.org/kingio/PHP-IMDB-Grabber) 

Or:

```bash
composer update
./vendor/bin/phpunit test/DataTest.php
```

# Changelog

https://github.com/kingio/PHP-IMDB-Grabber/releases

# License
MIT
