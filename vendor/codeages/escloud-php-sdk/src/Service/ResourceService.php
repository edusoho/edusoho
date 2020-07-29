<?php

namespace ESCloud\SDK\Service;

use ESCloud\SDK\Exception\ResponseException;
use ESCloud\SDK\Exception\SDKException;
use ESCloud\SDK\HttpClient\ClientException;
use phpDocumentor\Reflection\Types\String_;

class ResourceService extends BaseService
{
    protected $host = 'resource-service.qiqiuyun.net';
    protected $service = 'resource';

    /**
     * 获取表单上传的参数
     *
     * @param $params array 参数
     * @return array 上传表单参数
     * @throws ResponseException
     * @throws SDKException
     * @throws ClientException
     */
    public function startUpload(array $params)
    {
        return $this->request('POST', '/upload/start', $params);
    }

    /**
     * 完成表单上传
     *
     * @param $no string 云资源编号
     * @return array
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function finishUpload($no)
    {
        return $this->request('POST', '/upload/finish', array('no' => $no));
    }

    /**
     * @param string $no 云资源编号
     * @return array
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function get($no)
    {
        return $this->request('GET', '/resources/' . $no);
    }

    /**
     * @param array $params
     * @return array
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function search(array $params)
    {
        return $this->request('GET', '/resources', $params);
    }

    /**
     * @param array $params
     * @return array
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function sync(array $params)
    {
        return $this->request('GET', '/resources/sync', $params);
    }

    /**
     * @param string $no
     * @param array $params
     * @return string
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function getDownloadUrl($no, array $params = array())
    {
        return $this->request('GET', '/resources/' . $no . '/downloadUrl', $params);
    }

    /**
     * @param string $no
     * @param string $name
     * @return array
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function rename($no, $name)
    {
        return $this->request('PUT', '/resources/' . $no . '/name', ['name' => $name]);
    }

    /**
     * @param string $no
     * @return array
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function delete($no)
    {
        return $this->request('DELETE', '/resources/' . $no);
    }

    /**
     * @param string $no
     * @return mixed
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function getThumbnails($no)
    {
        return $this->request('GET', '/resources/' . $no . '/thumbnails');
    }
}
