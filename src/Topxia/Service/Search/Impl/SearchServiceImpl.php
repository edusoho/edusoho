<?php
namespace Topxia\Service\Search\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Search\SearchService;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Topxia\Service\Search\Adapter\SearchAdapterFactory;

class SearchServiceImpl extends BaseService implements SearchService
{
    public function cloudSearch($type, $conditions = array())
    {
        $api    = CloudAPIFactory::create('root');
        $result = $api->get('/search', $conditions);

        if (empty($result['success'])) {
            throw new \RuntimeException("搜索失败，请稍候再试.", 1);
        }

        $resultSet = $result['body']['datas'];
        $counts    = $result['body']['count'];

        $resultSet = SearchAdapterFactory::create($type)->adapt($resultSet);

        return array($resultSet, $counts);
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
