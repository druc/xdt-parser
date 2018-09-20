# xdt-parser

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Php parser for German healthcare data interchange formats GDT, LDT and BDT.

## Install

Via Composer

``` bash
$ composer require druc/xdt-parser
```

## Usage


```php
<?php
use Druc\XdtParser\XdtParser;

// Create instance
$this->parser = XdtParser::make(
    file_get_contents(__DIR__ . '/data/sample.ldt'), // file contents 
    ['id' => '8316', 'observation' => '6220'] // field mapping
);

// Check for corrupt file
$this->parser->isCorrupt(); // -> true/false

// Get first value matching the id field
$this->parser->first('id'); // --> '123'

// Get all values matching the observation field
$this->parser->find('observation'); // --> ['observation 1', 'observation 2'];

// Also works with the xdt code
$this->parser->first('8316'); // --> '123'
$this->parser->find('6220'); // --> ['observation 1', 'observation 2'];

// Get mapped values
$this->parser->getMapped(); // -> ['id' => 123, 'observation' => ['observation 1', 'observation 2']];

// Add extra field mapping
$this->parser->addFieldsMap(['my_value' => '3213']);

// Remove fields
$this->parser->removeFields(['id']);

// Get all values (mapped and unkown/unmapped values)
$this->parser->all(); // -> ['3213' => 'unkown code value', 'id' => 123, 'observation' => ['observation 1', 'observation 2']];

// Get field key
$this->parser->getKey('observation'); // -> 6220 if the mapping contains 'observation' => '6220'

// Get field name
$this->parser->getFieldName('6220'); // -> `observation` if the mapping contains 'observation' => '6220'

// Get xdtRows
$this->parser->getXdtRows(); // will return an array with the unparsed rows of your content: ['0346220Dies ist ein zweizeiliger', '0143102Frank']

// Parse single string
$this->parser->parseSingle('0346220Dies ist ein zweizeiliger'); // -> ['length' => 32, field = '6220', 'value' => 'Dies ist ein zweizeiliger'];
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email druc@pinsmile.com instead of using the issue tracker.

## Credits

- [Constantin Druc][link-author]
- js parser - [albertzak/xdt](https://github.com/albertzak/xdt)  
- ruby parser - [levinlex/xdt](https://github.com/levinalex/xdt)
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/druc/xdt-parser.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/druc/xdt-parser/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/druc/xdt-parser.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/druc/xdt-parser.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/druc/xdt-parser.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/druc/xdt-parser
[link-travis]: https://travis-ci.org/druc/xdt-parser
[link-scrutinizer]: https://scrutinizer-ci.com/g/druc/xdt-parser/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/druc/xdt-parser
[link-downloads]: https://packagist.org/packages/druc/xdt-parser
[link-author]: https://github.com/druc
[link-contributors]: ../../contributors
