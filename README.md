## XdtParser
Php parser for German healthcare data interchange formats GDT, LDT and BDT.

## Installation and usage
`composer require druc/xdt-parser`

```php
use Druc\XdtParser\XdtParser;

// Create instance
$this->parser = XdtParser::make(
    file_get_contents(__DIR__ . '/data/sample.ldt'), // file contents 
    ['id' => '8316', 'observation' => '6220'] // field mapping
);

// Check for corrupt file
$this->parser->isCorrupt() // -> true/false

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
```

## Credits
- js parser - [albertzak/xdt](https://github.com/albertzak/xdt)  
- ruby parser - [levinlex/xdt](https://github.com/levinalex/xdt)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.