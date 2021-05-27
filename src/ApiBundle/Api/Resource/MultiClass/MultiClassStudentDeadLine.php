<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassService;

class MultiClassStudentDeadLine extends AbstractResource
{
    /**
     * @param $multiClassId
     * @param $updateType
     *
     * @return bool[]
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN,ROLE_TEACHER")
     */
    public function update(ApiRequest $request, $multiClassId, $updateType)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, ['ids', 'deadline']) || !is_array($fields['ids']) || !in_array($updateType, ['day', 'date'])) {
            throw MultiClassException::MULTI_CLASS_DATA_FIELDS_MISSING();
        }

        $courseId = $multiClass['courseId'];

        if ('day' == $updateType) {
            $this->getCourseMemberService()->batchUpdateMemberDeadlinesByDay($courseId, $fields['ids'], $fields['deadline'], $fields['waveType']);
        } elseif ('date' == $updateType) {
            $this->getCourseMemberService()->batchUpdateMemberDeadlinesByDate($courseId, $fields['ids'], $fields['deadline']);
        }

        return ['success' => true];
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return MultiClassService
     */
    private function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }
}
