<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassService;

class MultiClassStudentDeadLine extends AbstractResource
{
    public function update(ApiRequest $request, $multiClassId, $updateType)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, ['ids', 'deadline']) || !is_array($fields['ids'])) {
            throw MultiClassException::MULTI_CLASS_DATA_FIELDS_MISSING();
        }

        $ids = $request->request->get('ids');
        $deadline = $request->request->get('ids');
        $courseId = $multiClass['courseId'];

        if ('day' == $updateType) {
            $this->getCourseMemberService()->batchUpdateMemberDeadlinesByDay($courseId, $ids, $deadline, $fields['waveType']);
        } elseif ('date' == $updateType) {
            $this->getCourseMemberService()->batchUpdateMemberDeadlinesByDate($courseId, $ids, $deadline);
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
