<?php

namespace Druc\XdtParser\Tests;

use Druc\XdtParser\XdtParser;
use PHPUnit\Framework\TestCase;

class XdtParserTest extends TestCase
{
    /** @var XdtParser */
    private $parser;

    protected function setUp()
    {
        parent::setUp();
        $fieldMap = ['id' => '8316', 'observation' => '6220'];
        $content = file_get_contents(__DIR__ . '/data/sample.ldt');
        $this->parser = XdtParser::make($content, $fieldMap);
    }

    public function testFindsValueFirstByFieldName()
    {
        $this->assertEquals('LZBD_SYS', $this->parser->first('id'));
    }

    /** @test */
    public function testFindsValueFirstByFieldId()
    {
        $this->assertEquals('LZBD_SYS', $this->parser->first('8316'));
    }

    /** @test */
    public function testFindsAllValuesWithTheSameFieldName()
    {
        $this->assertEquals([
            'Dies ist ein zweizeiliger',
            'Befund zur 24h-Blutdruckmessung.'
        ], $this->parser->find('observation'));
    }

    /** @test */
    public function testFindsAllValuesWithTheSameFieldId()
    {
        $this->assertEquals([
            'Dies ist ein zweizeiliger',
            'Befund zur 24h-Blutdruckmessung.'
        ], $this->parser->find('6220'));
    }

    /** @test */
    public function testChecksForCorruptFiles()
    {
        $this->parser = XdtParser::make(file_get_contents(__DIR__ . '/data/corrupt_sample.ldt'));
        $this->assertTrue($this->parser->isCorrupt());

        $this->parser = XdtParser::make(file_get_contents(__DIR__ . '/data/sample.ldt'));
        $this->assertFalse($this->parser->isCorrupt());
    }

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function testGetAllFieldsIncludingUnmapped()
    {
        $this->assertTrue(array_key_exists('observation', $this->parser->all()));
        $this->assertTrue(array_key_exists('8315', $this->parser->all()));
    }

    public function testSkipsEmptyLines()
    {
        $this->parser = XdtParser::make(file_get_contents(__DIR__ . '/data/empty_line_sample.ldt'));
        $this->assertFalse($this->parser->isCorrupt());
    }
}
