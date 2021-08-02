<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassGroupService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\User\Service\UserService;

class MultiClassGroup extends AbstractResource
{
    public function search(ApiRequest $request, $multiClassId)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $groups = $this->getMultiClassGroupService()->findGroupsByMultiClassId($multiClassId);
        $assistants = $this->getUserService()->findUsersByIds(ArrayToolkit::column($groups, 'assistant_id'));
        $assistants = ArrayToolkit::index($assistants, 'id');
        foreach ($groups as &$group){
            $group['assistantName'] = isset($assistants[$group['assistant_id']]) && $assistants[$group['assistant_id']] ? $assistants[$group['assistant_id']]['nickname'] : '';
        }

        return $groups;
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return MultiClassGroupService
     */
    protected function getMultiClassGroupService()
    {
        return $this->service('MultiClass:MultiClassGroupService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
