<?php

namespace Biz\File\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class VideoMediaStatusUpdateJob extends AbstractJob
{
    public function execute()
    {
        $results = $this->getUploadFileService()->getResourcesStatus($this->getJobArgs());

        if (isset($results['data']) && !empty($results['data'])) {
            $successNum = 0;
            foreach ($results['data'] as $result) {
                $file = $this->getUploadFileService()->setResourceConvertStatus($result['resourceNo'], $result);
                $attachment = $this->getUploadFileService()->setAttachmentConvertStatus($result['resourceNo'], $result);

                if (empty($file) && empty($attachment)) {
                    continue;
                }
                ++$successNum;
            }

            $this->getSchedulerService()->updateJob($this->id, ['args' => $results['next']]);
            $this->getLogService()->info(
                'upload_file',
                'update',
                'video media status update success',
                [
                    'success' => round($successNum / count($results['data']) * 100, 2).'%',
                ]);
        }
    }

    private function getJobArgs()
    {
        $jobArgs = $this->args;
        if (empty($jobArgs)) {
            $jobArgs = [
                'cursor' => 0,
                'start' => 0,
                'limit' => 1000,
            ];
        }

        return $jobArgs;
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->biz->service('File:UploadFileService');
    }

    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
