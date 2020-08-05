<?php

namespace ESCloud\SDK\Service;

/**
 * 短信服务
 */
class SmsService extends BaseService
{
    protected $host = 'sms-service.qiqiuyun.net';
    protected $leafHost = array('sms-service-leaf1.qiqiuyun.net', 'sms-service-leaf2.qiqiuyun.net');
    protected $service = 'sms';

    /**
     * 单发文本短信
     *
     * @see http://qiqiuyun.pages.codeages.net/api-doc/v1/resource/sms-service.html
     *
     * @param $params array 发送参数
     */
    public function sendToOne(array $params)
    {
        return $this->request('POST', '/messages', $params, array(), 'leaf');
    }

    /**
     * 群发文本短信
     *
     * @see http://qiqiuyun.pages.codeages.net/api-doc/v1/resource/sms-service.html
     *
     * @param $params array 发送参数
     */
    public function sendToMany(array $params)
    {
        return $this->request('POST', '/messages/batch_messages', $params, array(), 'leaf');
    }

    /**
     * 添加签名
     *
     * @see http://qiqiuyun.pages.codeages.net/api-doc/v1/resource/sms-service.html
     *
     * @param $params array 签名参数
     */
    public function addSign(array $params)
    {
        return $this->request('POST', '/signs', $params, array(), 'root');
    }

    /**
     * 添加签名
     *
     * @see http://qiqiuyun.pages.codeages.net/api-doc/v1/resource/sms-service.html
     *
     * @param $params array 模板参数
     */
    public function addTemplate(array $params)
    {
        return $this->request('POST', '/templates', $params, array(), 'root');
    }
}
