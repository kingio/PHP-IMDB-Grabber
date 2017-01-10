# PHP IMDB Grabber

[![Build Status](https://travis-ci.org/kingio/PHP-IMDB-Grabber.svg?branch=master)](https://travis-ci.org/kingio/PHP-IMDB-Grabber)

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

## [8.3.2] - 2017-10-01
### Fixed
Fix extract values from new Cast links

## [8.3.1] - 2016-08-04
### Fixed
getCastCredits() fixes

## [8.3.0] - 2016-08-03
### Added
Added getAgeRating() method

## [8.2.0] - 2016-08-02
### Added
Get distributors for TV series

## [8.1.0] - 2016-07-28
### Added
Added getCompanyCredits() and getCastCredits() methods

## [8.0.0] - 2016-07-08
### Changed
Changed return value of a getAkas

## [7.2.1] - 2016-07-06
### Added
Improved release premiere checker (premieres are ignored)

## [7.2.1] - 2016-07-06
### Added
Improved release premiere checker (premieres are ignored)

## [7.2.0] - 2016-07-06
### Added
Get releases data

## [7.1.0] - 2016-01-29
### Fixed
Updated to support IMDB's new UI

## [7.0.3] - 2016-01-12
### Changed
- Improved title cleanup

## [7.0.2] - 2016-01-11
### Changed
- Throw an exception when media type is not found 
- Updated tests

## [7.0.1] - 2015-09-23
### Fixed
- Fixed the possibility of getting an undefined index 

## [7.0.0] - 2015-08-31
### Changed
- psr4 autoload

### Fixed
- Fixed writers & directors not being fetched because where hidden with a "View more" link

# License
MIT
