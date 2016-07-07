<?php
namespace Topxia\Service\Search\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Search\SearchService;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Topxia\Service\Search\Adapter\SearchAdapterFactory;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;


class SearchServiceImpl extends BaseService implements SearchService
{
    public function cloudSearch($type, $conditions = array())
    {
        $api    = CloudAPIFactory::create('leaf');
        $result = $api->get('/search', $conditions);

        if (empty($result['success'])) {
            throw new \RuntimeException($this->getKernel()->trans('搜索失败，请稍候再试.'), 1);
        }

        if(empty($result['body']['datas'])){
            return array(array(), 0);
        }

        $resultSet = $result['body']['datas'];
        $counts    = $result['body']['count'];

        $resultSet = SearchAdapterFactory::create($type)->adapt($resultSet);

        return array($resultSet, $counts);
    }

    public function refactorAllDocuments()
    {
        $api    = CloudAPIFactory::create('root');
        $conditions = array('categorys'=>'course,user,thread,article');
        return $api->post('/search/refactor_documents', $conditions);
    }

    public function applySearchAccount($callbackRouteUrl)
    {

        $siteUrl        = $this->getSiteUrl();

        $api  = CloudAPIFactory::create('root');
        $urls = array(
            array('category' => 'course', 'url' => $siteUrl.'/api/courses?cursor=0&start=0&limit=100'),
            array('category' => 'lesson', 'url' => $siteUrl.'/api/lessons?cursor=0&start=0&limit=100'),
            array('category' => 'user', 'url' => $siteUrl.'/api/users?cursor=0&start=0&limit=100'),
            array('category' => 'thread', 'url' => $siteUrl.'/api/chaos_threads?cursor=0,0,0&start=0,0,0&limit=50'),
            array('category' => 'article', 'url' => $siteUrl.'/api/articles?cursor=0&start=0&limit=100')
        );
        $urls = urlencode(json_encode($urls));

        $callbackUrl = $siteUrl.$callbackRouteUrl;
        $sign = $this->getSignEncoder()->encodeSign($callbackUrl, $api->getAccessKey());
        $callbackUrl .= '?sign='.rawurlencode($sign);

        $result = $api->post("/search/accounts", array('urls' => $urls, 'callback' => $callbackUrl));

        if ($result['success']) {
            $this->setCloudSearchWaiting();
        }

        return !empty($result['success']);
    }

    protected function getSiteUrl()
    {
        $siteSetting        = $this->getSettingService()->get('site');
        $siteUrl            = $siteSetting['url'];
        if(strpos($siteUrl, 'http://') !== 0){
            $siteUrl ='http://'.$siteUrl;
        }
        return  rtrim(rtrim($siteUrl), '/');
    }

    protected function setCloudSearchWaiting()
    {
        $searchSetting  = array(
            'search_enabled' => 1, 
            'status'         =>'waiting'
        );
        $this->getSettingService()->set('cloud_search', $searchSetting);
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getSignEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }
}
