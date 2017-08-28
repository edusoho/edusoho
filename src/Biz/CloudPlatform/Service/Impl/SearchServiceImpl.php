<?php

namespace Biz\CloudPlatform\Service\Impl;

use Biz\BaseService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\SearchService;
use Biz\System\Service\SettingService;

class SearchServiceImpl extends BaseService implements SearchService
{
    public function notifyDelete($params)
    {
        $api = CloudAPIFactory::create('leaf');

        $args = array(
            'type' => 'delete',
            'accessKey' => $api->getAccessKey(),
            'category' => $params['category'],
            'id' => $params['id'],
        );

        $result = $api->post('/search/notifications', $args);

        return $result;
    }

    public function notifyUpdate($params)
    {
        $api = CloudAPIFactory::create('leaf');

        $args = array(
            'type' => 'update',
            'accessKey' => $api->getAccessKey(),
            'category' => $params['category'],
        );

        $result = $api->post('/search/notifications', $args);

        return $result;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
