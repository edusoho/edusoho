<?php

namespace Biz\DestroyAccount\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\DestroyAccount\Dao\DestroyAccountRecordDao;
use Biz\DestroyAccount\DestroyAccountException;
use Biz\DestroyAccount\Service\DestroyAccountRecordService;
use Biz\DestroyAccount\Service\DestroyedAccountService;
use Biz\User\Service\Impl\UserServiceImpl;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class DestroyAccountRecordServiceImpl extends BaseService implements DestroyAccountRecordService
{
    public function getDestroyAccountRecord($id)
    {
        return $this->getDestroyAccountRecordDao()->get($id);
    }

    public function updateDestroyAccountRecord($id, $fields)
    {
        if (!ArrayToolkit::requireds($fields, array('status'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $fields = ArrayToolkit::parts($fields, array('status', 'rejectedReason', 'auditUserId'));

        return $this->getDestroyAccountRecordDao()->update($id, $fields);
    }

    public function createDestroyAccountRecord($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('userId', 'nickname', 'reason'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (mb_strlen($fields['reason'], 'UTF-8') > 200) {
            $this->createNewException(DestroyAccountException::REASON_TOO_LONG());
        }

        $fields = ArrayToolkit::parts($fields, array('userId', 'nickname', 'reason'));

        return $this->getDestroyAccountRecordDao()->create($fields);
    }

    public function deleteDestroyAccountRecord($id)
    {
        return $this->getDestroyAccountRecordDao()->delete($id);
    }

    public function getLastDestroyAccountRecordByUserId($userId)
    {
        return $this->getDestroyAccountRecordDao()->getLastDestroyAccountRecordByUserId($userId);
    }

    public function searchDestroyAccountRecords($conditions, $orderBy, $start, $limit)
    {
        $records = $this->getDestroyAccountRecordDao()->search($conditions, $orderBy, $start, $limit);

        return $records;
    }

    public function countDestroyAccountRecords($conditions)
    {
        return $this->getDestroyAccountRecordDao()->count($conditions);
    }

    public function passDestroyAccountRecord($id)
    {
        $record = $this->getDestroyAccountRecord($id);
        $user = $this->getUserService()->getUser($record['userId']);
        $auditUser = $this->getCurrentUser();
        $fields = array(
            'auditUserId' => $auditUser['id'],
            'status' => 'passed',
        );

        try {
            $this->updateDestroyAccountRecord($id, $fields);
            $destroyedAccount = $this->getDestroyedAccountService()->createDestroyedAccount(array('recordId' => $record['id'], 'userId' => $record['userId'], 'nickname' => $record['nickname']));

            //更新用户相关信息
            $this->updateUserInfoForDestroyAccount($record['userId'], $destroyedAccount);
        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
        }

        $this->dispatchEvent('user.destroyed', new Event($user));

        return true;
    }

    public function rejectDestroyAccountRecord($id, $reason)
    {
        $record = $this->getDestroyAccountRecord($id);
        $auditUser = $this->getCurrentUser();
        $fields = array(
            'auditUserId' => $auditUser['id'],
            'status' => 'rejected',
            'rejectedReason' => $reason,
        );
        $user = $this->getUserService()->getUser($record['userId']);
        $this->updateDestroyAccountRecord($id, $fields);

        $this->dispatchEvent('user.reject.destroy', new Event($user, array('reason' => $reason)));

        return true;
    }

    private function updateUserInfoForDestroyAccount($userId, $destroyedAccount)
    {
        //更新用户信息
        $this->getUserService()->updateUserForDestroyedAccount($userId, $destroyedAccount['id']);

        //清除用户绑定信息
        $this->getUserService()->deleteUserBindByUserId($userId);

        //清除用户登录token
        $this->getTokenService()->destroyTokensByUserId($userId);

        return true;
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return DestroyedAccountService
     */
    protected function getDestroyedAccountService()
    {
        return $this->createService('DestroyAccount:DestroyedAccountService');
    }

    /**
     * @return DestroyAccountRecordDao
     */
    protected function getDestroyAccountRecordDao()
    {
        return $this->createDao('DestroyAccount:DestroyAccountRecordDao');
    }
}
