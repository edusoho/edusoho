<?php

namespace Biz\S2B2C\Service\Impl;

use Biz\BaseService;
use Biz\File\Service\Impl\SupplierFileImplementorImpl;
use Biz\S2B2C\Service\FileSourceService;
use Biz\S2B2C\Service\ProductService;

class FileSourceServiceImpl extends BaseService implements FileSourceService
{
    public function getFullFileInfo($file)
    {
        if (empty($file)) {
            return null;
        }
        $file['globalId'] = empty($file['s2b2cGlobalId']) ? $file['globalId'] : $file['s2b2cGlobalId'];
        $file['hashId'] = empty($file['s2b2cHashId']) ? $file['hashId'] : $file['s2b2cHashId'];

        $courseProduct = $this->getS2B2CProductService()->getByTypeAndLocalResourceId('course_set', $file['targetId']);

        $file['sourceTargetId'] = empty($courseProduct['remoteProductId']) ? 0 : $courseProduct['remoteProductId'];

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

    /**
     * @return ProductService
     */
    protected function getS2B2CProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }
}
