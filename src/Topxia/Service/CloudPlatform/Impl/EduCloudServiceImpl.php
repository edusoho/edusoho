<?php
namespace Topxia\Service\CloudPlatform\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\CloudPlatform\EduCloudService;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class EduCloudServiceImpl extends BaseService implements EduCloudService
{
    public function isHiddenCloud()
    {
        try {
            $api  = CloudAPIFactory::create('root');
            $overview = $api->get("/cloud/{$api->getAccessKey()}/overview");
        } catch (\RuntimeException $e) {
            $e->getMessage();
        }
        $status = $overview['accessCloud'] && $overview['enabled'];

        return $status;
    }
}