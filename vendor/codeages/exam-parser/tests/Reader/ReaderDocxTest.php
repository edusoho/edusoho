<?php

namespace ExamParser\Tests\Reader;

use ExamParser\Reader\ReadDocx;
use ExamParser\Tests\BaseTestCase;

class ReaderDocxTest extends BaseTestCase
{
    public function testReadSingleChoice()
    {
        $filename = dirname(__DIR__).'/Fixtures/files/single_choice.docx';
        $wordRead = new ReadDocx($filename);
        $text = $wordRead->read();
        $this->assertEquals(0, stripos('单选题', $text));
    }

    /**
     * @expectedException \ExamParser\Exception\ExamException
     */
    public function testReadDoc()
    {
        $filename = dirname(__DIR__).'/Fixtures/files/questions.doc';
        $wordRead = new ReadDocx($filename);
        $wordRead->read();
    }
}
