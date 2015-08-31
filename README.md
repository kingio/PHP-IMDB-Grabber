# PHP IMDB Grabber

[![Travis CI](https://img.shields.io/travis/kingio/PHP-IMDB-Grabber/custom.svg)](https://travis-ci.org/kingio/PHP-IMDB-Grabber)

This PHP library enables you to scrap data from IMDB.com and it's heavily based on 
[FabianBeiner's PHP-IMDB-Grabber](https://github.com/FabianBeiner/PHP-IMDB-Grabber) 
(You can also find more info about this package, its license and conditions of usage there)

# Disclaimer

*The script is a proof of concept. It’s mostly working, but you shouldn’t use it. IMDb doesn’t allow this method of data fetching. 
I personally do not use or promote this script, you’re fully responsive if you’re using it.*

# Install

`composer require kingio/php-imdb-grabber`

# Testing

See on [Travis CI](https://travis-ci.org/kingio/PHP-IMDB-Grabber) 

Or:

`composer update`
`./vendor/bin/phpunit test/DataTest.php`

# Changelog

## [7.0.0] - 2015-08-31
### Changed
- psr4 autoload

### Fixed
- Fixed writers & directors not being fetched because where hidden with a "View more" link

# License
MIT
