<?php

namespace QiQiuYun\SDK\Service;

/**
 * 微信服务
 */
class WeChatService extends BaseService
{
    protected $host = 'wt-service.qiqiuyun.net';

    /**
     * 获取预授权URL
     *
     * @param $platformType int     必填 （1：公众号  2：小程序）
     * @param $callbackUrl  string  必填  授权回调地址
     *
     * @return array
     *               url  string 预授权URL
     */
    public function getPreAuthUrl($platformType, $callbackUrl)
    {
        return $this->request('GET', "/open_platform/{$platformType}/pre_auth_url", array('callbackUrl' => $callbackUrl));
    }

    /**
     * 获取服务号的所有用户，分页
     *
     * @param $nextOpenId string 选填 从这个OPENID开始取用户数据
     *
     * @return array 返回参数如下：
     *               total string  总数
     *               count string  当前取出数量
     *               data  array   参数如下：
     *               openId  array openId集合
     *
     *      next_openid  string 最后一个openId
     */
    public function getUserList($nextOpenId = '')
    {
        return $this->request('GET', '/official_account/user_list', array('nextOpenId' => $nextOpenId));
    }

    /**
     * 获取微信单个用户信息
     *
     * @param $openId string 必填 微信openId
     *        $lang   string 选填 语言（zh_CN 简体，zh_TW 繁体，en 英语，默认zh_CN）
     *
     * @return array 返回参数如下
     *               subscribe       int     1：表示用户关注该公众号
     *               openid          string  微信openId（对当前公众号唯一）
     *               nickname        string  微信名
     *               sex             string  性别（1：男 2：女）
     *               language        string  语言
     *               city            string  城市
     *               province        string  省
     *               country         string  国家
     *               headimgurl      string  头像地址
     *               subscribe_time  string  关注时间
     *               unionid         string  只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段
     *               remark          string  管理员对该微信用户的备注
     *               groupid         int     微信用户所在分组ID
     *               tagid_list      array   微信用户拥有标签Id列表
     *               subscribe_scene string  用户关注渠道
     *               qr_scene        int     二维码扫码场景
     *               qr_scene_str    string  二维码扫码场景描述
     */
    public function getUserInfo($openId, $lang = 'zh_CN')
    {
        return $this->request('GET', '/official_account/user_list', array('openId' => $openId, 'lang' => $lang));
    }

    /**
     * 获取微信多个用户信息
     *
     * @param $openIds array 必填 微信openIds
     *        $lang   string 选填 语言（zh_CN 简体，zh_TW 繁体，en 英语，默认zh_CN）
     *
     * @return array 返回参数如下
     *               user_info_list  array  数据内容为 多个getUserInfo返回参数集合
     */
    public function batchGetUserInfo($openIds, $lang = 'zh_CN')
    {
        $result = $this->request('POST', '/official_account/user_infos', array('openIds' => $openIds, 'lang' => $lang));

        return !empty($result['user_info_list']) ? $result['user_info_list'] : array();
    }

    /**
     * 获取授权方允许的授权列表
     *
     * @param $platformType int 必填 （1：公众号  2：小程序）
     *
     * @return array 例如：
     *               [
     *               {
     *               "funcscope_category":{
     *               "id":1
     *               }
     *               },
     *               {
     *               "funcscope_category":{
     *               "id":4
     *               }
     *               }
     *               ]
     */
    public function getAuthorizationInfo($platformType)
    {
        return $this->request('GET', "/open_platform/{$platformType}/authorization_info", array());
    }

    /**
     * @param $templateCode string 必填 模版识别码
     *
     * @return array 返回参数如下
     *               template_id  创建的模版id
     */
    public function createNotificationTemplate($templateCode)
    {
        return $this->request('POST', "/notification_template/{$templateCode}");
    }

    /**
     * @param $templateId string 必填 模版id
     *
     * @return array 返回参数如下
     *               success bool 是否删除成功
     */
    public function deleteNotificationTemplate($templateId)
    {
        return $this->request('DELETE', "/notification_template/{$templateId}");
    }
}
