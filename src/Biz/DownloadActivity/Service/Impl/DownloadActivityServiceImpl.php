<?php

namespace Biz\DownloadActivity\Service\Impl;

use Biz\BaseService;
use Biz\DownloadActivity\Dao\DownloadFileRecordDao;
use Biz\DownloadActivity\Service\DownloadActivityService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

class DownloadActivityServiceImpl extends BaseService implements DownloadActivityService
{
    public function createDownloadFileRecord($downloadFile)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw new AccessDeniedException();
        }
        $record = array(
            'downloadActivityId' => $downloadFile['downloadActivityId'],
            'downloadFileId'     => $downloadFile['id'],
            'fileIndicate'       => $downloadFile['indicate'],
            'userId'             => $user->getId()
        );

        return $this->getDownloadFileRecordDao()->create($record);
    }

    /**
     * @return DownloadFileRecordDao
     */
    protected function getDownloadFileRecordDao()
    {
        return $this->createDao('DownloadActivity:DownloadFileRecordDao');
    }

}