<?php

namespace QiQiuYun\SDK\Service;

/**
 * Push服务
 */
class PushService extends BaseService
{
    protected $host = 'push-service.76.cg-dev.cn';

    /**
     * 设备注册
     *
     * @see http://coding.codeages.net/qiqiuyun/api-doc/blob/master/v2/push.md
     *
     * $params array $params 注册信息
     *
     * @return array 注册信息
     */
    public function registerDevices($params)
    {
        return $this->request('POST', '/devices', $params);
    }

    /**
     * 状态变更
     *
     * @see http://coding.codeages.net/qiqiuyun/api-doc/blob/master/v2/push.md
     *
     * @param string $regId 网校设备id
     *
     * @return array 设备信息
     */
    public function updateDeviceState($regId, $params)
    {
        return $this->request('POST', "/devices/{$regId}", $params);
    }

    /**
     * 发送通知
     *
     * @see http://coding.codeages.net/qiqiuyun/api-doc/blob/master/v2/push.md
     *
     * @param array $messages 消息信息
     *
     * @return array
     */
    public function notifications($messages)
    {
        return $this->request('POST', '/notifications', $messages);
    }
}
