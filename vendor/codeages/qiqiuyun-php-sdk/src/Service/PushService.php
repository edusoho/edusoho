<?php

namespace QiQiuYun\SDK\Service;

/**
 * AI服务
 */
class PushService extends BaseService
{
    protected $host = 'push-service.qiqiuyun.net';

    /**
     * @params $params array 注册参数
     *   params 参数如下：
     *      provider 推送消息供应商,
     *      provider_reg_id 供应商返回的reg_id,
     *      device_token 设备编号,
     *      os 设备类型 android or ios,
     *      os_version 设备系统版本号,
     *      model 手机型号
     *
     * @return array 返回参数如下：
     *      reg_id 云平台生成的reg_id,
     *      is_active 是否活跃,
     *      device_token 设备编号,
     *      os 设备类型 android or ios,
     *      os_version 设备系统版本号,
     *      model 手机型号
     */
    public function registerDevice($params)
    {
        return $this->request('POST', '/devices', $params);
    }

    /**
     * @param $regId string 注册时返回的注册号
     * @param $isActive int 设备是否活跃 1 or 0
     *
     * @return array
     */
    public function setDeviceActive($regId, $isActive)
    {
        return $this->request('POST', "/devices/{$regId}/active", array('is_active' => $isActive));
    }

    /**
     * @param $params array 发送消息参数
     *   params 参数如下：
     *      reg_ids 云平台生成的reg_id,最多10个
     *      pass_through_type 消息传达类型，分消息栏消息normal，和透传消息transparency
     *      payload 透传消息体，透传是必传
     *      title 通知标题
     *      description 通知描述
     * @return array 返回参数如下：
     *      push_id 推送批次的id
     *
     */
    public function pushMessage($params)
    {
        return $this->request('POST', '/notifications', $params);
    }
}
