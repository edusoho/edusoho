<?php
namespace Topxia\Service\Quiz\Tests;

use Topxia\Service\Common\BaseTestCase;

// TODO

class QuestionServiceTest extends BaseTestCase
{   

    public function testQuestionXXX()
    {
       $this->assertNull(null);
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Quiz.QuestionService');
    }

}