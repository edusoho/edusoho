<?php

namespace ApiBundle\Api\Resource\Account;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\EncryptionToolkit;
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

        $user = $this->getCurrentUser();
        $fiedlds = array(
            'userId' => $user['id'],
            'nickname' => $user['nickname'],
            'reason' => $reason,
        );

        return $this->getDestroyAccountRecordService()->createDestroyAccountRecord($fiedlds);
    }

    /**
     * @return DestroyAccountRecordService
     */
    private function getDestroyAccountRecordService()
    {
        return $this->service('DestroyAccount:DestroyAccountRecordService');
    }
}