<?php

namespace Tests\Item;

use Codeages\Biz\ItemBank\Item\ItemParser;
use Tests\IntegrationTestCase;

class ItemParserTest extends IntegrationTestCase
{
    public function testParserSingleChoice()
    {
        $filename = dirname(__DIR__).'/Item/Fixtures/single_choice.docx';
        $parser = new ItemParser($this->biz);

        $text = $parser->read($filename, ['resourceTmpPath' => '/tmp']);
        $result = $parser->parse($text);

        $this->assertEquals('single_choice', $result[0]['type']);
        $this->assertCount(1, $result);
        $this->assertNotEmpty($result[0]['questions']);
    }

    public function testParserChoice()
    {
        $filename = dirname(__DIR__).'/Item/Fixtures/choice.docx';
        $parser = new ItemParser($this->biz);

        $text = $parser->read($filename, ['resourceTmpPath' => '/tmp']);
        $result = $parser->parse($text);

        $this->assertEquals('choice', $result[0]['type']);
        $this->assertCount(1, $result);
        $this->assertNotEmpty($result[0]['questions']);
    }

    public function testParserUncertainChoice()
    {
        $filename = dirname(__DIR__).'/Item/Fixtures/uncertain_choice.docx';
        $parser = new ItemParser($this->biz);

        $text = $parser->read($filename, ['resourceTmpPath' => '/tmp']);
        $result = $parser->parse($text);

        $this->assertEquals('uncertain_choice', $result[0]['type']);
        $this->assertCount(1, $result);
        $this->assertNotEmpty($result[0]['questions']);
    }

    public function testParserDetermine()
    {
        $filename = dirname(__DIR__).'/Item/Fixtures/determine.docx';
        $parser = new ItemParser($this->biz);

        $text = $parser->read($filename, ['resourceTmpPath' => '/tmp']);
        $result = $parser->parse($text);

        $this->assertEquals('determine', $result[0]['type']);
        $this->assertCount(1, $result);
        $this->assertNotEmpty($result[0]['questions']);
    }

    public function testParserFill()
    {
        $filename = dirname(__DIR__).'/Item/Fixtures/fill.docx';
        $parser = new ItemParser($this->biz);

        $text = $parser->read($filename, ['resourceTmpPath' => '/tmp']);
        $result = $parser->parse($text);

        $this->assertEquals('fill', $result[0]['type']);
        $this->assertCount(1, $result);
        $this->assertNotEmpty($result[0]['questions']);
    }

    public function testParserEssay()
    {
        $filename = dirname(__DIR__).'/Item/Fixtures/essay.docx';
        $parser = new ItemParser($this->biz);

        $text = $parser->read($filename, ['resourceTmpPath' => '/tmp']);
        $result = $parser->parse($text);

        $this->assertEquals('essay', $result[0]['type']);
        $this->assertCount(1, $result);
        $this->assertNotEmpty($result[0]['questions']);
    }

    public function testParserMaterial()
    {
        $filename = dirname(__DIR__).'/Item/Fixtures/material.docx';
        $parser = new ItemParser($this->biz);

        $text = $parser->read($filename, ['resourceTmpPath' => '/tmp']);
        $result = $parser->parse($text);

        $this->assertEquals('material', $result[0]['type']);
        $this->assertCount(1, $result);
        $this->assertNotEmpty($result[0]['questions']);
    }

    public function testParser_whenItemError_thenReturnError()
    {
        $filename = dirname(__DIR__).'/Item/Fixtures/material.docx';
        $parser = new ItemParser($this->biz);

        $text = $parser->read($filename, ['resourceTmpPath' => '/tmp']);
        $result = $parser->parse($text);

        $this->assertEquals('material', $result[0]['type']);
        $this->assertCount(1, $result);
        $this->assertNotEmpty($result[0]['errors']);
        $this->assertNotEmpty($result[0]['questions']);
    }
}
