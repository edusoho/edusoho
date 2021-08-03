<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\Common\CommonException;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassGroupService;
use Biz\MultiClass\Service\MultiClassRecordService;
use Biz\MultiClass\Service\MultiClassService;

class MultiClassStudentGroup extends AbstractResource
{
    public function update(ApiRequest $request, $multiClassId, $groupId)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $group = $this->getMultiClassGroupService()->getById($groupId);
        if (empty($group)) {
            throw MultiClassException::GROUP_NOT_FOUND();
        }

        $user = $this->getCurrentUser();
        if (!$user->hasPermission('admin_v2_education')) {
            throw new AccessDeniedException();
        }

        $studentIds = $request->request->get('studentIds', []);
        if (empty($studentIds)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        return $this->getAssistantStudentService()->batchUpdateStudentsGroup($multiClassId, $studentIds, $groupId);

        return [
            'success' => true,
            'message' => '变更分组成功',
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
     * @return MultiClassGroupService
     */
    private function getMultiClassGroupService()
    {
        return $this->service('MultiClass:MultiClassGroupService');
    }

    /**
     * @return MultiClassRecordService
     */
    private function getMultiClassRecordService()
    {
        return $this->service('MultiClass:MultiClassRecordService');
    }
}
