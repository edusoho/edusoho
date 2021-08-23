<?php

namespace Biz\MultiClass\Service\Impl;

use Biz\Assistant\Service\AssistantStudentService;
use Biz\BaseService;
use Biz\MultiClass\Dao\MultiClassRecordDao;
use Biz\MultiClass\Service\MultiClassGroupService;
use Biz\MultiClass\Service\MultiClassRecordService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\SCRM\Service\SCRMService;
use Biz\User\Service\UserService;
use Ramsey\Uuid\Uuid;

class MultiClassRecordServiceImpl extends BaseService implements MultiClassRecordService
{
    public function searchRecord($conditions, $orderBys, $start, $limit)
    {
        return $this->getMultiClassRecordDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function batchUpdateRecords($records)
    {
        if (empty($records)) {
            return;
        }

        return $this->getMultiClassRecordDao()->batchUpdate(array_column($records, 'id'), $records);
    }

    public function batchCreateRecords($records)
    {
        if (empty($records)) {
            return;
        }

        return $this->getMultiClassRecordDao()->batchCreate($records);
    }

    public function createRecord($userId, $multiClassId)
    {
        $relation = $this->getAssistantStudentService()->getByStudentIdAndMultiClassId($userId, $multiClassId);
        if (empty($relation)) {
            return;
        }

        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            return;
        }

        $assistant = $this->getUserService()->getUser($relation['assistantId']);
        if (empty($relation['group_id'])) {
            $content = sprintf('加入班课(%s), 分配助教(%s)', $multiClass['title'], $assistant['nickname']);
        } else {
            $group = $this->getMultiClassGroupService()->getMultiClassGroup($relation['group_id']);
            $content = sprintf('加入班课(%s)的%s, 分配助教(%s)', $multiClass['title'], $group['name'], $assistant['nickname']);
        }

        $record = [
            'user_id' => $userId,
            'assistant_id' => $relation['assistantId'],
            'multi_class_id' => $multiClassId,
            'data' => ['title' => '加入班课', 'content' => $content],
            'sign' => $this->makeSign(),
            'is_push' => 0,
        ];

        $record = $this->getMultiClassRecordDao()->create($record);

        $this->uploadRecord($record);

        return $record;
    }

    public function uploadRecord($record)
    {
        if (!$this->getSCRMService()->isSCRMBind()) {
            return;
        }

        $assistant = $this->getUserService()->getUser($record['assistant_id']);
        $user = $this->getUserService()->getUser($record['user_id']);
        if (empty($assistant['scrmStaffId']) || empty($user['scrmUuid'])) {
            return;
        }

        try {
            $list = [[
                'ticket' => $record['sign'],
                'title' => $record['data']['title'],
                'content' => $record['data']['content'],
                'customerUniqueId' => $user['scrmUuid'],
                'staffId' => (int) $assistant['scrmStaffId'],
            ]];

            $result = $this->getSCRMService()->uploadSCRMUserData($list);
            if ($result['ok']) {
                $this->getMultiClassRecordDao()->update($record['id'], ['is_push' => 1]);
            }
        } catch (\Exception $e) {
        }
    }

    public function makeSign()
    {
        $sign = time().'_'.Uuid::uuid4();
        $record = $this->getMultiClassRecordDao()->getRecordBySign($sign);
        if ($record) {
            $sign = $this->makeSign();
        }

        return $sign;
    }

    /**
     * @return MultiClassRecordDao
     */
    protected function getMultiClassRecordDao()
    {
        return $this->createDao('MultiClass:MultiClassRecordDao');
    }

    /**
     * @return AssistantStudentService
     */
    protected function getAssistantStudentService()
    {
        return $this->createService('Assistant:AssistantStudentService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->createService('MultiClass:MultiClassService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return SCRMService
     */
    protected function getSCRMService()
    {
        return $this->createService('SCRM:SCRMService');
    }

    /**
     * @return MultiClassGroupService
     */
    protected function getMultiClassGroupService()
    {
        return $this->createService('MultiClass:MultiClassGroupService');
    }
}
