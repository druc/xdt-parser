<?php

namespace Druc\XdtParser\Test;

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

    /** @test */
    public function it_finds_value_first_by_field_name()
    {
        $this->assertEquals('LZBD_SYS', $this->parser->first('id'));
    }

    /** @test */
    public function it_finds_value_first_by_field_id()
    {
        $this->assertEquals('LZBD_SYS', $this->parser->first('8316'));
    }

    /** @test */
    public function it_finds_all_values_with_the_same_field_name()
    {
        $this->assertEquals([
            'Dies ist ein zweizeiliger',
            'Befund zur 24h-Blutdruckmessung.'
        ], $this->parser->find('observation'));
    }

    /** @test */
    public function it_finds_all_values_with_the_same_field_id()
    {
        $this->assertEquals([
            'Dies ist ein zweizeiliger',
            'Befund zur 24h-Blutdruckmessung.'
        ], $this->parser->find('6220'));
    }

    /** @test */
    public function it_checks_for_corrupt_files()
    {
        $this->parser = XdtParser::make(file_get_contents(__DIR__ . '/data/corrupt_sample.ldt'));
        $this->assertTrue($this->parser->isCorrupt());

        $this->parser = XdtParser::make(file_get_contents(__DIR__ . '/data/sample.ldt'));
        $this->assertFalse($this->parser->isCorrupt());
    }

    /** @test */
    public function it_gets_mapped_values()
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
    public function unfindable_fields_are_set_to_null_when_get_mapped_is_called()
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
    public function can_add_extra_field_key_maps()
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
    public function removes_field_key_maps()
    {
        $this->parser->removeFields(['id']);

        $this->assertEquals([
            'observation' => [
                'Dies ist ein zweizeiliger',
                'Befund zur 24h-Blutdruckmessung.'
            ],
        ], $this->parser->getMapped());
    }
}
