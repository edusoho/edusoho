<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use Biz\S2B2C\Service\ProductService;

class S2b2cProductDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $localResource)
    {
        if (!ArrayToolkit::requireds($localResource, ['id', 'type'])) {
            throw new \InvalidArgumentException('Id or type not exist in local resource');
        }

        return $this->getS2b2cProductService()->getByTypeAndLocalResourceId($localResource['type'], $localResource['id']);
    }

    /**
     * @return ProductService
     */
    protected function getS2b2cProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }
}
