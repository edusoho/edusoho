<?php

namespace Biz\DestroyAccount\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\DestroyAccount\Dao\DestroyAccountRecordDao;
use Biz\DestroyAccount\DestroyAccountException;
use Biz\DestroyAccount\Service\DestroyAccountRecordService;
use Biz\DestroyAccount\Service\DestroyedAccountService;
use Biz\System\Service\LogService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;

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

        $fields = ArrayToolkit::parts($fields, array('status', 'rejectedReason', 'auditUserId', 'auditTime'));

        return $this->getDestroyAccountRecordDao()->update($id, $fields);
    }

    public function createDestroyAccountRecord($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('userId', 'nickname', 'reason', 'ip'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $user = $this->getCurrentUser();
        $lastAuditRecord = $this->getLastAuditDestroyAccountRecordByUserId($user['id']);
        if (!empty($lastAuditRecord)) {
            $this->createNewException(DestroyAccountException::AUDIT_RECORD_EXIST());
        }

        if (mb_strlen($fields['reason'], 'UTF-8') > 200) {
            $this->createNewException(DestroyAccountException::REASON_TOO_LONG());
        }

        $fields = ArrayToolkit::parts($fields, array('userId', 'nickname', 'reason', 'ip'));
        $fields['status'] = 'audit';

        return $this->getDestroyAccountRecordDao()->create($fields);
    }

    public function deleteDestroyAccountRecord($id)
    {
        return $this->getDestroyAccountRecordDao()->delete($id);
    }

    public function cancelDestroyAccountRecord()
    {
        $user = $this->getCurrentUser();
        $lastAuditRecord = $this->getLastAuditDestroyAccountRecordByUserId($user['id']);
        if (empty($lastAuditRecord)) {
            $this->createNewException(DestroyAccountException::NOT_FOUND_RECORD());
        }

        return $this->updateDestroyAccountRecord($lastAuditRecord['id'], array('status' => 'cancel'));
    }

    public function getLastAuditDestroyAccountRecordByUserId($userId)
    {
        return $this->getDestroyAccountRecordDao()->getLastAuditDestroyAccountRecordByUserId($userId);
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
            'auditTime' => time(),
        );

        try {
            $this->beginTransaction();
            $record = $this->updateDestroyAccountRecord($id, $fields);
            $destroyedAccount = $this->getDestroyedAccountService()->createDestroyedAccount(array('recordId' => $record['id'], 'userId' => $record['userId'], 'nickname' => $record['nickname']));

            //更新用户相关信息
            $this->updateUserInfoForDestroyAccount($record['userId'], $destroyedAccount);
            $this->getLogService()->info('destroy_account_record', 'pass', '通过注销帐号申请', array('destroyedAccountId' => $destroyedAccount['id'], 'nickname' => $record['nickname'], 'reason' => $record['reason'], 'auditUserNickname' => $auditUser['nickname'], 'mobile' => $user['verifiedMobile'], 'email' => $user['email']));
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
            $this->rollback();
        }

        $this->dispatchEvent('user.destroyed', new Event($user));

        return $record;
    }

    public function rejectDestroyAccountRecord($id, $reason)
    {
        $record = $this->getDestroyAccountRecord($id);
        $auditUser = $this->getCurrentUser();
        $fields = array(
            'auditUserId' => $auditUser['id'],
            'status' => 'rejected',
            'rejectedReason' => $reason,
            'auditTime' => time(),
        );
        $user = $this->getUserService()->getUser($record['userId']);
        $record = $this->updateDestroyAccountRecord($id, $fields);

        $this->dispatchEvent('user.reject.destroy', new Event($user, array('reason' => $reason)));
        $this->getLogService()->info('destroy_account_record', 'reject', '拒绝注销帐号申请', array('auditUserNickname' => $auditUser['nickname'], 'nickname' => $record['nickname'], 'reason' => $record['reason'], 'rejectedReason' => $reason));

        return $record;
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

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
