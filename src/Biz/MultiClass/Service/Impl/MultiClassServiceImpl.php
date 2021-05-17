<?php

namespace Biz\MultiClass\Service\Impl;

use Biz\BaseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Dao\MultiClassDao;
use Biz\MultiClass\Service\MultiClassService;

class MultiClassServiceImpl extends BaseService implements MultiClassService
{
    public function createMultiClass($multiClass)
    {
        $teacherId = [
            ['id' => $multiClass['teacherId']],
        ];
        $assistantIds = $multiClass['assistantIds'];
        $multiClass = $this->multiClassFilter($multiClass);

        $multiClass = $this->getMultiClassDao()->create($multiClass);
        $this->getCourseMemberService()->setCourseTeachers($multiClass['courseId'], $teacherId, $multiClass['id']);
        $this->getCourseMemberService()->setMultiClassAssistant($multiClass['courseId'], $assistantIds, $multiClass['id']);

        return $multiClass;
    }

    public function getMultiClassByTitle($title)
    {
        return $this->getMultiClassDao()->getMultiClassByTitle($title);
    }

    private function multiClassFilter($multiClass)
    {
        if (!empty($multiClass['teacherId'])) {
            unset($multiClass['teacherId']);
        }
        if (!empty($multiClass['assistantIds'])) {
            unset($multiClass['assistantIds']);
        }

        return $multiClass;
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return MultiClassDao
     */
    protected function getMultiClassDao()
    {
        return $this->createDao('MultiClass:MultiClassDao');
    }
}
