<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\UserException;

class UserLoginLog extends AbstractResource
{
    public function search(ApiRequest $request, $userId)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isSuperAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        return $this->getLogService()->searchLogs(['action' => 'login_success', 'userId' => $userId], 'createdByAsc', $offset, $limit);
    }

    /**
     * @return \Biz\System\Service\Impl\LogServiceImpl
     */
    private function getLogService()
    {
        return $this->service('System:LogService');
    }
}
