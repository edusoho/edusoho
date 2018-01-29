<?php

namespace QiQiuYun\SDK\Service;

/**
 * 短信服务
 */
class SmsService extends BaseService
{
    protected $host = 'sms-service.qiqiuyun.net';

    /**
     * 单发文本短信
     *
     * @see http://qiqiuyun.pages.codeages.net/api-doc/v1/resource/sms-service.html
     *
     * @param $params array 发送参数
     */
    public function sendToOne(array $params)
    {
        return $this->request('POST', '/messages', $params);
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
        return $this->request('POST', '/messages/batch_messages', $params);
    }
}
