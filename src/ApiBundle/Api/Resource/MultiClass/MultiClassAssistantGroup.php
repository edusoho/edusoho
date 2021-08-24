<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Assistant\AssistantException;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\Common\CommonException;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassGroupService;
use Biz\MultiClass\Service\MultiClassService;

class MultiClassAssistantGroup extends AbstractResource
{
    public function update(ApiRequest $request, $multiClassId, $assistantId)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $multiClassMember = $this->getCourseMemberService()->getMemberByMultiClassIdAndUserId($multiClassId, $assistantId);
        if (empty($multiClassMember) || 'assistant' != $multiClassMember['role']) {
            throw AssistantException::ASSISTANT_NOT_FOUND();
        }

        $user = $this->getCurrentUser();
        if (!$user->hasPermission('admin_v2_education')) {
            throw new AccessDeniedException();
        }

        $groupIds = $request->request->get('groupIds', []);
        if (empty($groupIds)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $this->getMultiClassGroupService()->batchUpdateGroupAssistant($multiClassId, $groupIds, $assistantId);

        return [
            'success' => true,
            'message' => '变更分组助教成功',
        ];
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

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return MultiClassGroupService
     */
    private function getMultiClassGroupService()
    {
        return $this->service('MultiClass:MultiClassGroupService');
    }
}
