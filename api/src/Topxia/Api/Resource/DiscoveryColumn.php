<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class DiscoveryColumn extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $result = $this->getDiscoveryColumnService()->getAllDiscoveryColumns();

        if (empty($result)) {
            return $this->error('error', '暂无分类内容!');
        }
      
        return $this->wrap($result, sizeof($result));
    }

    public function filter($res)
    {
        return $res;
    }

    protected function getDiscoveryColumnService()
    {
        return $this->getServiceKernel()->createService('DiscoveryColumn.DiscoveryColumnService');
    }
}
