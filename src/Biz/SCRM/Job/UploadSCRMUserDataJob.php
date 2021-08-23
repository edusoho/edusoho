<?php

namespace Biz\SCRM\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\MultiClass\Service\MultiClassRecordService;
use Biz\SCRM\Service\SCRMService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Dao\JobDao;

class UploadSCRMUserDataJob extends AbstractJob
{
    public function execute()
    {
        $isScrmBind = $this->getSCRMService()->isSCRMBind();
        if (empty($isScrmBind)) {
            return;
        }

        try {
            $this->biz['db']->beginTransaction();
            $records = $this->getMultiClassRecordService()->searchRecord(['isPush' => 0], [], 0, PHP_INT_MAX);
            $assistants = $this->getUserService()->findUsersByIds(ArrayToolkit::column($records, 'assistant_id'));
            $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($records, 'user_id'));
            $updateData = [];
            $uploadList = [];
            foreach ($records as $record) {
                if (empty($assistants[$record['assistant_id']]['scrmStaffId']) || empty($users[$record['user_id']]['scrmUuid'])) {
                    continue;
                }

                $user = $users[$record['user_id']];
                $assistant = $assistants[$record['assistant_id']];
                $uploadList[] = [
                    'ticket' => $record['sign'],
                    'title' => $record['data']['title'],
                    'content' => $record['data']['content'],
                    'customerUniqueId' => $user['scrmUuid'],
                    'staffId' => (int) $assistant['scrmStaffId'],
                ];

                $updateData[] = [
                    'id' => $record['id'],
                    'is_push' => 1,
                ];
            }

            $result = $this->getSCRMService()->uploadSCRMUserData($uploadList);
            if ($result['ok']) {
                $this->getMultiClassRecordService()->batchUpdateRecords($updateData);
            }

            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
        }
    }

    /**
     * @return MultiClassRecordService
     */
    private function getMultiClassRecordService()
    {
        return $this->biz->service('MultiClass:MultiClassRecordService');
    }

    /**
     * @return SCRMService
     */
    private function getSCRMService()
    {
        return $this->biz->service('SCRM:SCRMService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return JobDao
     */
    protected function getJobDao()
    {
        return $this->biz->dao('Scheduler:JobDao');
    }
}
