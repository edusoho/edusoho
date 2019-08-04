<?php

namespace ExamParser\Tests\Parser;

use ExamParser\Parser\Parser;
use ExamParser\Reader\ReadDocx;
use ExamParser\Tests\BaseTestCase;

class ParserTest extends BaseTestCase
{
    public function testParser()
    {
        $filename = dirname(__DIR__).'/Fixtures/files/example1.docx';
        $wordRead = new ReadDocx($filename);
        $text = $wordRead->read();
        $parser = new Parser($text);
        $parser->parser();
    }
}
