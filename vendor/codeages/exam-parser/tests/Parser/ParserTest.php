<?php

namespace ExamParser\Tests\Parser;

use ExamParser\Parser\Parser;
use ExamParser\Reader\ReadDocx;
use ExamParser\Tests\BaseTestCase;

class ParserTest extends BaseTestCase
{
    public function testParserSingleChoice()
    {
        $filename = dirname(__DIR__).'/Fixtures/files/single_choice.docx';
        $wordRead = new ReadDocx($filename);
        $text = $wordRead->read();
        $parser = new Parser($text);
        $questions = $parser->parser();
        $this->assertCount(1, $questions);
        $this->assertEquals(0, stripos('单选题', $questions[0]['stem']));
        $this->assertEquals('single_choice', $questions[0]['type']);
    }

    public function testParserChoice()
    {
        $filename = dirname(__DIR__).'/Fixtures/files/choice.docx';
        $wordRead = new ReadDocx($filename);
        $text = $wordRead->read();
        $parser = new Parser($text);
        $questions = $parser->parser();
        $this->assertCount(1, $questions);
        $this->assertEquals(0, stripos('多选题', $questions[0]['stem']));
        $this->assertEquals('choice', $questions[0]['type']);
    }

    public function testParserUncertainChoice()
    {
        $filename = dirname(__DIR__).'/Fixtures/files/uncertain_choice.docx';
        $wordRead = new ReadDocx($filename);
        $text = $wordRead->read();
        $parser = new Parser($text);
        $questions = $parser->parser();
        $this->assertCount(1, $questions);
        $this->assertEquals(0, stripos('不定项', $questions[0]['stem']));
        $this->assertEquals('uncertain_choice', $questions[0]['type']);
    }

    public function testParserEssay()
    {
        $filename = dirname(__DIR__).'/Fixtures/files/essay.docx';
        $wordRead = new ReadDocx($filename);
        $text = $wordRead->read();
        $parser = new Parser($text);
        $questions = $parser->parser();
        $this->assertCount(1, $questions);
        $this->assertEquals(0, stripos('问答题', $questions[0]['stem']));
        $this->assertEquals('essay', $questions[0]['type']);
    }

    public function testParserFill()
    {
        $filename = dirname(__DIR__).'/Fixtures/files/fill.docx';
        $wordRead = new ReadDocx($filename);
        $text = $wordRead->read();
        $parser = new Parser($text);
        $questions = $parser->parser();
        $this->assertCount(1, $questions);
        $this->assertEquals(0, stripos('填空题', $questions[0]['stem']));
        $this->assertEquals('fill', $questions[0]['type']);
        $this->assertEquals(array('太白', '青莲居士|谪仙人'), $questions[0]['answers']);
    }

    public function testParserDetermine()
    {
        $filename = dirname(__DIR__).'/Fixtures/files/determine.docx';
        $wordRead = new ReadDocx($filename);
        $text = $wordRead->read();
        $parser = new Parser($text);
        $questions = $parser->parser();
        $this->assertCount(1, $questions);
        $this->assertEquals(0, stripos('判断题', $questions[0]['stem']));
        $this->assertEquals('determine', $questions[0]['type']);
        $this->assertEquals(true, $questions[0]['answer']);
    }

    public function testParserMaterial()
    {
        $filename = dirname(__DIR__).'/Fixtures/files/material.docx';
        $wordRead = new ReadDocx($filename);
        $text = $wordRead->read();
        $parser = new Parser($text);
        $questions = $parser->parser();
        $this->assertCount(1, $questions);
        $this->assertEquals(0, stripos('材料题', $questions[0]['stem']));
        $this->assertEquals('material', $questions[0]['type']);
        $this->assertEquals(2, count($questions[0]['subQuestions']));
    }

    public function testParserChoiceWithNoStem()
    {
        $filename = dirname(__DIR__).'/Fixtures/files/choice_with_no_stem.docx';
        $wordRead = new ReadDocx($filename);
        $text = $wordRead->read();
        $parser = new Parser($text);
        $questions = $parser->parser();
        $this->assertCount(1, $questions);
        $this->assertEmpty($questions[0]['stem']);
        $this->assertEquals('choice', $questions[0]['type']);
    }
}
