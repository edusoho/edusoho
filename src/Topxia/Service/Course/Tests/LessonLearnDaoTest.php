<?php
namespace Topxia\Service\Course\Tests;

use Biz\BaseTestCase;;
use Topxia\Common\ArrayToolkit;

class LessonLearnDaoTest extends BaseTestCase
{
	public function testAnalysisLessonFinishedDataByTime()
    {
    	$this->getLessonLearnDao()->analysisLessonFinishedDataByTime(time(), time());
    }

    protected function getLessonLearnDao()
    {
        return $this->getServiceKernel()->createDao('Course:LessonLearnDao'); 
    }
}