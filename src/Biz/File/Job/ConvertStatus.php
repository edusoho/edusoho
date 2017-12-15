<?php

namespace Biz\File\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class ConvertStatus extends AbstractJob
{
    public function execute()
    {
        try {
            $this->biz['db']->beginTransaction();

            $results = $this->getUploadFileService()->getResourcesStatuses(array('cursor' => time()));

            if (isset($results['data']) && !empty($results['data'])) {
                foreach ($results['data'] as $result) {
                    $file = $this->getUploadFileService()->getFileByGlobalId($result['resourceNo']);
                    $this->getUploadFileService()->setAudioConvertStatus($file['id'],$file['audioConvertStatus']);
                }
            }

            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
        }
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->biz->service('File:UploadFileService');
    }
}
