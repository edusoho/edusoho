<?php

namespace Biz\MultiClass\Service\Impl;

use Biz\BaseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Dao\MultiClassDao;
use Biz\MultiClass\Service\MultiClassService;

class MultiClassServiceImpl extends BaseService implements MultiClassService
{
    public function createMultiClass($fields)
    {
        $teacherId = [
            ['id' => $fields['teacherId']],
        ];
        $assistantIds = $fields['assistantIds'];
        $fields = $this->multiClassFieldsFilter($fields);

        $multiClass = $this->getMultiClassDao()->create($fields);
        $this->getCourseMemberService()->setCourseTeachers($fields['courseId'], $teacherId, $multiClass['id']);
        $this->getCourseMemberService()->setMultiClassAssistant($fields['courseId'], $assistantIds, $multiClass['id']);

        return $multiClass;
    }

    public function updateMultiClass($id, $fields)
    {
        $teacherId = [
            ['id' => $fields['teacherId']],
        ];
        $assistantIds = $fields['assistantIds'];
        $fields = $this->multiClassFieldsFilter($fields);

        $multiClass = $this->getMultiClassDao()->update($id, $fields);
        $this->getCourseMemberService()->setCourseTeachers($fields['courseId'], $teacherId, $multiClass['id']);
        $this->getCourseMemberService()->setMultiClassAssistant($fields['courseId'], $assistantIds, $multiClass['id']);

        return $multiClass;
    }

    public function getMultiClassByTitle($title)
    {
        return $this->getMultiClassDao()->getByTitle($title);
    }

    private function multiClassFieldsFilter($fields)
    {
        if (!empty($fields['teacherId'])) {
            unset($fields['teacherId']);
        }
        if (!empty($fields['assistantIds'])) {
            unset($fields['assistantIds']);
        }

        return $fields;
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
