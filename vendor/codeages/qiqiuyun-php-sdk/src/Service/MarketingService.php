<?php

namespace QiQiuYun\SDK\Service;

use QiQiuYun\SDK\SignUtil;

class MarketingService extends BaseService
{
    protected $baseUri = 'http://fx.yxdev.com';

    public function generateLoginForm($user, $site)
    {
        $jsonStr = SignUtil::serialize(['user' => $user, 'site' => $site]);
        
        $sign = SignUtil::sign($this->auth, $jsonStr);
        
        return $this->generateForm($user, $site, $sign);
    }

    /**
     * 给分销平台发送数据
     *
     * @param $suffix 如 /merchant_students
     * @param $data, 格式为 json数据数组
     *  [
     *      {
     *          'd' => 1,
     *          'c' => 2,
     *      },
     *      .....
     *  ]
     */
    public function postDistributorJsonArrayData($suffix, $data)
    {
        $jsonStr = SignUtil::serializeJsonArrayAndCut($data);

        return $this->postDistributorData($suffix, $data, $jsonStr);
    }

    /**
     * 给分销平台发送数据
     *
     * @param $data, 格式为 json数据数组
     *  {
     *      'd' => 1,
     *      'c' => 2,
     *  }
     */
    public function postDistributorJsonData($suffix, $data)
    {
        $jsonStr = SignUtil::serializeJsonAndCut($data);

        return $this->postDistributorData($suffix, $data, $jsonStr);
    }

    private function generateForm($user, $site, $sign)
    {
        $url = $this->baseUri;
        return "
            <form class='form-horizontal' id='login-form' method='post' action='{$url}'>
                <fieldset style='display:none;'>
                    <input type='hidden' name='site[name]' class='form-control' value={$site['name']}>
                    <input type='hidden' name='site[logo]' class='form-control' value={$site['logo']}>
                    <input type='hidden' name='site[about]' class='form-control' value={$site['about']}>
                    <input type='hidden' name='site[wechat]' class='form-control' value={$site['wechat']}>
                    <input type='hidden' name='site[qq]' class='form-control' value={$site['qq']}>
                    <input type='hidden' name='site[telephone]' class='form-control' value={$site['telephone']}>
                    <input type='hidden' name='site[domain]' class='form-control' value={$site['domain']}>
                    <input type='hidden' name='site[access_key]' class='form-control' value='{$site['access_key']}'>
                    <input type='hidden' name='user[user_source_id]' class='form-control' value='{$user['user_source_id']}'>
                    <input type='hidden' name='user[nickname]' class='form-control' value='{$user['nickname']}'>
                    <input type='hidden' name='user[avatar]' class='form-control' value='{$user['avatar']}'>
                    <input type='hidden' name='sign' class='form-control' value='{$sign}'>
                </fieldset>
                <button type='submit' class='btn btn-primary'>提交</button>
            </form>";
    }

    private function postDistributorData($suffix, $data, $jsonStr)
    {
        $sign = SignUtil::sign($this->auth, $jsonStr);

        return $this->client->request(
            'POST',
            $this->baseUri . $suffix,
            array(
                'data' => $data,
                'sign' => $sign,
            )
        );
    }
}
