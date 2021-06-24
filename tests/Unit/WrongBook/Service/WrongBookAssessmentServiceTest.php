<?php

namespace Tests\Unit\WrongBook\Service;

use Biz\BaseTestCase;
use Biz\WrongBook\Service\WrongBookAssessmentService;

class WrongBookAssessmentServiceTest extends BaseTestCase
{
    public function testCreateAssessment()
    {
        return $this->getWrongBookAssessmentService()->createAssessment([]);
    }

    /**
     * @return WrongBookAssessmentService
     */
    protected function getWrongBookAssessmentService()
    {
        return $this->getBiz()->service('WrongBook:WrongBookAssessmentService');
    }
}
