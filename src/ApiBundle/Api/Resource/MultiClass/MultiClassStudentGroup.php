<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassService;

class MultiClassStudentGroup extends AbstractResource
{
    public function update(ApiRequest $request, $multiClassId, $groupId)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $studentIds = $request->request->get('studentIds', []);
        if (empty($studentIds)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }


    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return MultiClassService
     */
    private function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return AssistantStudentService
     */
    private function getAssistantStudentService()
    {
        return $this->service('Assistant:AssistantStudentService');
    }
}
