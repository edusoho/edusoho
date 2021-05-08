<?php

namespace ESCloud\SDK\Service;

use ESCloud\SDK\Exception\ResponseException;
use ESCloud\SDK\Exception\SDKException;
use ESCloud\SDK\HttpClient\ClientException;

class SearchService extends BaseService
{
    protected $host = 'search-service.qiqiuyun.net';
    protected $service = 'search';


    /**git
     * 接入云搜索账号接口
     * @return mixed
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function createAccount()
    {
        return $this->request('POST', '/accounts');
    }

    /**
     * 上报数据接口
     * @param $category
     * @param $resources
     * @return mixed
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function report($category, $resources)
    {
        return $this->request('POST', '/report/' . $category, $resources);
    }

    /**
     * 数据删除接口
     * @param $category
     * @param $id
     * @return string
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function deleteData($category, $id)
    {
        return $this->request('DELETE', '/report/' . $category . '/' . $id);
    }

    /**
     * @param $categories
     * @return array
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function restartReport($categories)
    {
        return $this->request('POST', '/report/restart', $categories);
    }


    /**
     * @param $categories
     * @return mixed
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function restartReportFinish($categories)
    {
        return $this->request('POST', '/report/restart_finish', $categories);
    }

    /**
     * @param $params
     * @return mixed
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function search($params)
    {
        return $this->request('GET', 'search', $params);
    }
}
