<?php

namespace Druc\XdtParser\Tests;

use Druc\XdtParser\CorruptedXdt;
use Druc\XdtParser\XdtParser;
use PHPUnit\Framework\TestCase;

class XdtParserTest extends TestCase
{
    /** @var XdtParser */
    private $parser;
    private $fieldMap;

    protected function setUp()
    {
        parent::setUp();
        $this->fieldMap = ['id' => '8316', 'observation' => '6220'];
        $content = file_get_contents(__DIR__ . '/data/sample.ldt');
        $this->parser = XdtParser::make($content, $this->fieldMap);
    }

    public function testFindsValueFirstByFieldName()
    {
        $this->assertEquals('LZBD_SYS', $this->parser->first('id'));
    }

    public function testFindsValueFirstByFieldId()
    {
        $this->assertEquals('LZBD_SYS', $this->parser->first('8316'));
    }

    public function testFindsAllValuesWithTheSameFieldName()
    {
        $this->assertEquals([
            'Dies ist ein zweizeiliger',
            'Befund zur 24h-Blutdruckmessung.'
        ], $this->parser->find('observation'));
    }

    public function testFindsAllValuesWithTheSameFieldId()
    {
        $this->assertEquals([
            'Dies ist ein zweizeiliger',
            'Befund zur 24h-Blutdruckmessung.'
        ], $this->parser->find('6220'));
    }

    public function testThrowsExceptionOnCorruptedFiles()
    {
        $this->expectException(CorruptedXdt::class);
        $this->parser = XdtParser::make(file_get_contents(__DIR__ . '/data/corrupt_sample.ldt'));
    }

    public function testGetMapped()
    {
        $this->assertEquals([
            'id' => 'LZBD_SYS',
            'observation' => [
                'Dies ist ein zweizeiliger',
                'Befund zur 24h-Blutdruckmessung.'
            ]
        ], $this->parser->getMapped());
    }

    public function testUnfindableFieldsAreSetToNullOnGetMapped()
    {
        $this->parser->setFieldsMap([
            'id' => '8316',
            'observation' => '6220',
            'non-existing-identifier' => 'non-exist'
        ]);

        $this->assertEquals([
            'id' => 'LZBD_SYS',
            'observation' => [
                'Dies ist ein zweizeiliger',
                'Befund zur 24h-Blutdruckmessung.'
            ],
            'non-existing-identifier' => null
        ], $this->parser->getMapped());
    }

    public function testAddExtraFieldKeyMaps()
    {
        $this->parser->addFieldsMap([
            'first_name' => '3101',
            'last_name' => '3102'
        ]);

        $this->assertEquals([
            'id' => 'LZBD_SYS',
            'observation' => [
                'Dies ist ein zweizeiliger',
                'Befund zur 24h-Blutdruckmessung.'
            ],
            'first_name' => 'Mustermann',
            'last_name' => 'Frank'
        ], $this->parser->getMapped());
    }

    public function testRemoveFieldKeyMaps()
    {
        $this->parser->removeFields(['id']);

        $this->assertEquals([
            'observation' => [
                'Dies ist ein zweizeiliger',
                'Befund zur 24h-Blutdruckmessung.'
            ],
        ], $this->parser->getMapped());
    }

    public function testGetAllFieldsIncludingUnmapped()
    {
        $this->assertTrue(array_key_exists('observation', $this->parser->all()));
        $this->assertTrue(array_key_exists('8315', $this->parser->all()));
    }

    public function testParseSingle()
    {
        $this->assertEquals([
            'length' => 19,
            'key' => '3101',
            'value' => 'Mustermann',
        ], $this->parser->parseSingle('0193101Mustermann'));
    }

    public function testGetKey()
    {
        $this->assertEquals('6220', $this->parser->getKey('observation'));
    }

    public function testGetFieldName()
    {
        $this->assertEquals('observation', $this->parser->getFieldName('6220'));
    }
    
    public function testGetXdtRows()
    {
        $this->parser = XdtParser::make('0193101Mustermann' . PHP_EOL . '0036202Dsq');
        $this->assertEquals(['0193101Mustermann', '0036202Dsq'], $this->parser->getXdtRows());
    }

    public function testParsesFilesWithEmptyLines()
    {
        $this->parser = XdtParser::make(file_get_contents(__DIR__ . '/data/empty_line_sample.ldt'), $this->fieldMap);
        $this->assertEquals('LZBD_SYS', $this->parser->first('id'));
    }
}
