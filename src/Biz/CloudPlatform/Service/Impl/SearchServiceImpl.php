<?php

namespace Biz\CloudPlatform\Service\Impl;

use Biz\BaseService;
use Biz\CloudPlatform\Client\FailoverCloudAPI;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\SearchService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;

class SearchServiceImpl extends BaseService implements SearchService
{
    protected $cloudLeafApi;

    protected $cloudRootApi;

    public function __construct(Biz $biz)
    {
        parent::__construct($biz);
        $this->cloudLeafApi = CloudAPIFactory::create('leaf');
        $this->cloudRootApi = CloudAPIFactory::create('root');
    }

    public function notifyDelete($params)
    {
        $api = $this->getCloudApi('leaf');

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
        $api = $this->getCloudApi('leaf');

        $args = array(
            'type' => 'update',
            'accessKey' => $api->getAccessKey(),
            'category' => $params['category'],
        );

        $result = $api->post('/search/notifications', $args);

        return $result;
    }

    /**
     * @param $node
     *
     * @return FailoverCloudAPI
     */
    protected function getCloudApi($node)
    {
        $apiProp = 'cloud'.ucfirst($node).'Api';

        return $this->$apiProp;
    }

    /**
     * @param $node
     * @param $api
     * 仅供单元测试使用，正常业务严禁使用
     */
    public function setCloudApi($node, $api)
    {
        $apiProp = 'cloud'.ucfirst($node).'Api';
        $this->$apiProp = $api;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
