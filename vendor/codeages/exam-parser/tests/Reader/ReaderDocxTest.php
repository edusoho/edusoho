<?php

namespace ExamParser\Tests\Reader;

use ExamParser\Reader\ReadDocx;
use ExamParser\Tests\BaseTestCase;

class ReaderDocxTest extends BaseTestCase
{
    public function testRead()
    {
        $filename = dirname(__DIR__).'/Fixtures/files/example1.docx';
        $wordRead = new ReadDocx($filename);
    }

    public function testReadDoc()
    {
        $filename = dirname(__DIR__).'/Fixtures/files/example1.docx';
        $wordRead = new ReadDocx($filename);
        $text = $wordRead->read();

        $tmpName = '/tmp/'.time().'.text';

        file_put_contents($tmpName, $text);
    }
}
