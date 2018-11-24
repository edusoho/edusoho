<?php

namespace Biz\Face\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Face\Service\FaceService;
use AppBundle\Common\ArrayToolkit;

class FaceServiceImpl extends BaseService implements FaceService
{
    public function getAiFaceSdk()
    {
        return $this->biz['qiQiuYunSdk.aiface'];
    }

    public function createFaceLog($log)
    {
        if (!ArrayToolkit::requireds($log, array('userId', 'status'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $log = ArrayToolkit::parts(
            $log,
            array(
                'userId',
                'status',
                'sessionId',
            )
        );

        return $this->getFaceLogDao()->create($log);
    }

    public function countFaceLog($conditions)
    {
        return $this->getFaceLogDao()->count($conditions);
    }

    protected function getFaceLogDao()
    {
        return $this->createDao('Face:FaceLogDao');
    }
}
