<?php

namespace ApiBundle\Api\Resource\Account;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\DestroyAccount\Service\DestroyAccountRecordService;
use Biz\User\Service\UserService;

class AccountApplyDestroy extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $reason = $request->request->get('reason', '');

        if (empty($reason)) {
            throw CommonException::ERROR_PARAMETER();
        }

        $user = $this->getUserService()->getUserAndProfile($this->getCurrentUser()->getId());
        $fields = [
            'userId' => $user['id'],
            'nickname' => $user['nickname'],
            'mobile' => $user['verifiedMobile'] ?: $user['mobile'],
            'email' => $user['email'],
            'reason' => $reason,
            'ip' => $request->getHttpRequest()->getClientIp(),
        ];

        return $this->getDestroyAccountRecordService()->createDestroyAccountRecord($fields);
    }

    /**
     * @return DestroyAccountRecordService
     */
    private function getDestroyAccountRecordService()
    {
        return $this->service('DestroyAccount:DestroyAccountRecordService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
