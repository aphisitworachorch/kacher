[![Kacher Logo](kacher-logo.png)]()
# Kacher

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Travis](https://img.shields.io/travis/aphisitworachorch/kacher.svg?style=flat-square)]()
[![Total Downloads](https://img.shields.io/packagist/dt/aphisitworachorch/kacher.svg?style=flat-square)](https://packagist.org/packages/aphisitworachorch/kacher)

## Install
`composer require aphisitworachorch/kacher`

## Usage
- For listing all tables in database : php artisan dbml:list (custom type --custom)
- For Parse from DB to DBML : php artisan dbml:parse (custom type --custom / dbdocs abilities --dbdocs)

## Customizable Type
- Store file in /storage/app/custom_type.json
- Example
  - { "type": "target_type" }

## Credits

- [Arsanandha Aphisitworachorch](https://github.com/aphisitworachorch)
- [All Contributors](https://github.com/aphisitworachorch/kacher/contributors)

## Security
If you discover any security-related issues, please email arsanandha.ap@gmail.com instead of using the issue tracker.

## License
The MIT License (MIT). Please see [License File](/LICENSE.md) for more information.
