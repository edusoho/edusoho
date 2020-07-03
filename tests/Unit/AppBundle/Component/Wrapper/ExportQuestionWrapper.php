<?php

namespace Tests\Unit\AppBundle\Component\Wrapper;

use Biz\BaseTestCase;
use AppBundle\Component\Wrapper\ExportQuestionWrapper;

class ExportQuestionWrapperTest extends BaseTestCase
{
    public function testSeq()
    {
        $wrapper = $this->getExportQuestionWrapper();
        $question = [
            'seq' => 1
        ];
        
        $result = $wrapper->seq($question);

        $this->assertEquals('1、', $result['seq']);
    }

    public function testNum()
    {
        $wrapper = $this->getExportQuestionWrapper();
        
        $question = [
            'seq' => 1
        ];
        $result = $wrapper->num($question);
        
        $this->assertArrayEquals($result, $question);

        $question = [
            'num' => 1,
        ];
        $result = $wrapper->num($question);

        $this->assertEquals('1、', $result['num']);
    }

    public function testStem()
    {
        $wrapper = $this->getExportQuestionWrapper();
        $question = [
            'stem' => '<p>你好</p><a>世界</a>'
        ];
        $result = $wrapper->stem($question);

        $this->assertEquals('你好<a>世界</a>', $result['stem'][0]['content']);

        $question = [
            'stem' => '<p>你好</p><img src="test.png">世界'
        ];
        $result = $wrapper->stem($question);

        $this->assertEquals('你好', $result['stem'][0]['content']);
        $this->assertEquals('世界', $result['stem'][2]['content']);
    }

    public function testOptions()
    {
        $wrapper = $this->getExportQuestionWrapper();
        $question = [
            'stem' => '<p>你好</p><a>世界</a>'
        ];
        $result = $wrapper->options($question);

        $this->assertArrayEquals([], $result);

        $question = [
            'metas' => [
                'choices' => [
                    'A' => '<p>你好</p><a>世界</a>',
                    'B' => '<p>你好</p><img src="test.png">世界'
                ]
            ]
        ];
        $result = $wrapper->options($question);
        
        $this->assertEquals('A.你好<a>世界</a>', $result['options']['A'][0]['content']);
    }

    private function getExportQuestionWrapper()
    {
        return new ExportQuestionWrapper(self::getContainer());
    }
}