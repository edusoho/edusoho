<?php

namespace Biz\File\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class ConvertStatus extends AbstractJob
{
    public function execute()
    {
        $results = $this->getUploadFileService()->getResourcesStatuses(array('cursor' => $this->getNextTime()));

        if (isset($results['data']) && !empty($results['data'])) {
            $successNum = 0;
            foreach ($results['data'] as $result) {
                $file = $this->getUploadFileService()->setResourceConvertStatus($result['resourceNo'], $result);

                if (empty($file)) {
                    continue;
                }
                $successNum++;
            }

            $this->getJobDao()->update($this->id, array('args' => $results['next']));
            $this->getLogService()->info(
                'upload_file',
                'check_convert_status',
                '刷新音视频转码状态的定时任务执行成功',
                array(
                    'success' => round($successNum / count($results['data']) * 100, 2).'%',
                ));
        }
    }

    private function getNextTime()
    {
        $jobArgs = $this->args;
        if (empty($jobArgs)) {
            $jobArgs = array();
        }

        return empty($jobArgs['cursor']) ? 0 : $jobArgs['cursor'];
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->biz->service('File:UploadFileService');
    }

    protected function getJobDao()
    {
        return $this->biz->dao('Scheduler:JobDao');
    }

    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
