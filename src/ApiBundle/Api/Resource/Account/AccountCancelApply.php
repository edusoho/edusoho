<?php

namespace ApiBundle\Api\Resource\Account;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\DestroyAccount\Service\DestroyAccountRecordService;

class AccountCancelApply extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $lastAuditRecord = $this->getDestroyAccountRecordService()->cancelDestroyAccountRecord();

        if ($lastAuditRecord['status'] == 'cancel') {
            return true;
        }

        return false;
    }

    /**
     * @return DestroyAccountRecordService
     */
    private function getDestroyAccountRecordService()
    {
        return $this->service('DestroyAccount:DestroyAccountRecordService');
    }
}
