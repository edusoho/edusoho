<?php

namespace ApiBundle\Api\Resource\App;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseSetService;
use Biz\DiscoveryColumn\Service\DiscoveryColumnService;
use Biz\System\Service\SettingService;
use ApiBundle\Api\Annotation\ApiConf;

class AppChannel extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        return $this->getDiscoveryColumnService()->getDisplayData();
    }

    /**
     * @return DiscoveryColumnService
     */
    private function getDiscoveryColumnService()
    {
        return $this->service('DiscoveryColumn:DiscoveryColumnService');
    }
}