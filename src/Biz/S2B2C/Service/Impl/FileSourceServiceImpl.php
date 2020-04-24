<?php

namespace Biz\S2B2C\Service\Impl;

use Biz\BaseService;
use Biz\File\Service\Impl\SupplierFileImplementorImpl;
use Biz\S2B2C\Service\FileSourceService;

class FileSourceServiceImpl extends BaseService implements FileSourceService
{
    public function getFullFileInfo($file)
    {
        $file['globalId'] = empty($file['s2b2cGlobalId']) ? $file['globalId'] : $file['s2b2cGlobalId'];
        $file['hashId'] = empty($file['s2b2cHashId']) ? $file['hashId'] : $file['s2b2cHashId'];

//        TODO: 获取课程中的同步信息
        $course['s2b2cDistributeId'] = 412;

        $file['sourceTargetId'] = empty($course['s2b2cDistributeId']) ? 0 : $course['s2b2cDistributeId'];

        return $file;
    }

    public function player($globalId, $ssl = false)
    {
        return $this->getSupplierFileImplementor()->player($globalId, $ssl);
    }

    /**
     * @return SupplierFileImplementorImpl
     */
    protected function getSupplierFileImplementor()
    {
        return $this->createService('File:SupplierFileImplementor');
    }
}
