<?php

namespace ESCloud\SDK\Service;

use ESCloud\SDK\Exception\SDKException;

class NotificationService extends BaseService
{
    const SN_MAX_COUNT = 50;

    protected $host = 'notification-service.qiqiuyun.net';
    protected $service = 'notification';

    /**
     * @return array 返回Account
     *               * "id" ID,
     *               * "status" 0：关闭 1：开启, 成功开启为1
     *               * "created_time" "2019-06-06T09:55:28+00:00",
     *               * "updated_time" "2019-06-09T07:44:12+00:00"
     */
    public function openAccount()
    {
        return $this->request('POST', '/accounts');
    }

    /**
     * @return array 返回Account
     *               * "id" ID,
     *               * "status" 0：关闭 1：开启, 成功关闭为0
     *               * "created_time" "2019-06-06T09:55:28+00:00",
     *               * "updated_time" "2019-06-09T07:44:12+00:00"
     */
    public function closeAccount()
    {
        return $this->request('DELETE', '/accounts');
    }

    /**
     * @param $channelType
     *        wechat 微信,...
     * @param $params
     *        options app_id app_secret
     *
     * @return array 返回 Channel
     *               * "user_id" 云用户ID,
     *               * "type" "wechat" 微信,...
     *               * "status" 0：关闭 1：开启, 成功开启为1
     *               * "created_time" "2019-06-06T09:55:28+00:00",
     *               * "updated_time" "2019-06-09T08:01:23+00:00"
     */
    public function openChannel($channelType, $params)
    {
        $params['type'] = $channelType;

        return $this->request('POST', '/channels', $params);
    }

    /**
     * @param $channelType wechat 微信,...
     *
     * @return array
     *               * "user_id" 云用户ID,
     *               * "type" "wechat" 微信,...
     *               * "status" 0：关闭 1：开启, 成功关闭为0
     *               * "created_time" "2019-06-06T09:55:28+00:00",
     *               * "updated_time" "2019-06-09T08:01:23+00:00"
     */
    public function closeChannel($channelType)
    {
        return $this->request('DELETE', "/channels/{$channelType}");
    }

    /**
     * @param $notifications
     * [
     *   {
     *       "channel" : "wechat",
     *       "to_id": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M",
     *       "title": "xxx",
     *       "content": "xxxxxx",
     *       "template_id": "xxxxx",
     *       "template_args": {
     *           "first": {
     *               "value":"恭喜你购买成功！",
     *               "color":"#173177"
     *           },
     *           "keyword1":{
     *               "value":"巧克力",
     *               "color":"#173177"
     *           },
     *           "keyword2": {
     *               "value":"39.8元",
     *               "color":"#173177"
     *           },
     *           "keyword3": {
     *               "value":"2014年9月22日",
     *               "color":"#173177"
     *           },
     *           "remark":{
     *               "value":"欢迎再次购买！",
     *               "color":"#173177"
     *           }
     *       },
     *       "goto": {
     *           "type": "url/miniprogram", 必填
     *           "url": "xxxx",
     *           "appid": "xxxxxx",
     *           "pagepath": "xxxxx",
     *       },
     *   },
     *   ...
     * ]
     *
     * @return array
     *               * ["sn" : "XXXXXXXXX"] 批次SN，用于查询
     */
    public function sendNotifications($notifications)
    {
        return $this->request('POST', '/notifications', $notifications);
    }

    /**
     * @param $sn
     *        批次SN 用于查询
     *
     * @return array
     *               {
     *               "sn": "d54676fa85f211e9a177186590d302a3", //SN
     *               "total_count": 1, 总数
     *               "succeed_count": 0, 成功的数量
     *               "failure_reason": null, [{"code": "521002", "count": "1", "message": "未知错误，请联系云服务供应商"}]
     *               "is_finished": "1",
     *               "finished_time": "1970-01-01T00:00:00+00:00",
     *               "created_time": "2019-06-03T11:29:21+00:00",
     *               "updated_time": "2019-06-03T11:29:21+00:00"
     *               }
     */
    public function getNotification($sn)
    {
        return $this->request('GET', "/notifications/{$sn}");
    }

    /**
     * @param $conditions |sns|
     * @param int $offset
     * @param int $limit
     *
     * @return array
     *               Notification 集合
     */
    public function searchNotifications($conditions, $offset = 0, $limit = 30)
    {
        $params = array_merge($conditions, array('offset' => $offset, 'limit' => $limit));

        return $this->request('GET', '/notifications', $params);
    }

    /**
     * @param $sns
     *
     * @return array
     *               Notification 集合
     *
     * @throws SDKException
     */
    public function batchGetNotifications($sns)
    {
        if (self::SN_MAX_COUNT == count($sns)) {
            throw new SDKException('sn count out of limit');
        }

        $params = array(
            'sns' => $sns,
            'offset' => 0,
            'limit' => count($sns),
        );

        return $this->request('GET', '/notifications', $params);
    }
}
