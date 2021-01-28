<?php

namespace AppBundle\Component\Payment\Wxpay;

/**
 * JSAPI支付实现类
 * 该类实现了从微信公众平台获取code、通过code获取openid和access_token、
 * 生成jsapi支付js接口所需的参数、生成获取共享收货地址所需的参数.
 *
 * 该类是微信支付提供的样例程序，商户可根据自己的需求修改，或者使用lib中的api自行开发
 *
 * @author widy
 */
class JsApiPay
{
    private $config;

    private $request;

    private $curl_timeout = 10;

    private $mockedCurl = null;

    public function __construct($config, $request)
    {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * 网页授权接口微信服务器返回的数据，返回样例如下
     * {
     *  "access_token":"ACCESS_TOKEN",
     *  "expires_in":7200,
     *  "refresh_token":"REFRESH_TOKEN",
     *  "openid":"OPENID",
     *  "scope":"SCOPE",
     *  "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
     * }
     * 其中access_token可用于获取共享收货地址
     * openid是微信支付jsapi支付接口必须的参数.
     *
     * @var array
     */
    public $data = null;

    /**
     * 通过跳转获取用户的openid，跳转流程如下：
     * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
     * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code.
     *
     * @return 用户的openid
     */
    public function getOpenid()
    {
        //缓存$openid
        $openid = $this->request->getSession()->get('openid');
        if ($openid) {
            return $openid;
        }
        //通过code获得openid
        $code = $this->request->query->get('code');
        if (empty($code)) {
            //触发微信返回code码
            $url = $this->__createOauthUrlForCode();
            header("Location: $url");
            exit();
        } else {
            //获取code码，以获取openid
            $code = $this->request->query->get('code');
            $openid = $this->getOpenidFromMp($code);
            $this->request->getSession()->set('openid', $openid);

            return $openid;
        }
    }

    /**
     * 通过code从工作平台获取openid机器access_token.
     *
     * @param string $code 微信跳转回来带上的code
     *
     * @return openid
     */
    public function getOpenidFromMp($code)
    {
        $url = $this->__createOauthUrlForOpenid($code);

        if (empty($this->mockedCurl)) {
            //初始化curl
            $ch = curl_init();
            //设置超时
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //运行curl，结果以jason形式返回
            $res = curl_exec($ch);
            curl_close($ch);
            //取出openid
            $data = json_decode($res, true);

            return $data['openid'];
        }

        return $this->mockedCurl['openid'];
    }

    /**
     * 拼接签名字符串.
     *
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private function toUrlParams($urlObj)
    {
        $buff = '';
        foreach ($urlObj as $k => $v) {
            if ('sign' != $k) {
                $buff .= $k.'='.$v.'&';
            }
        }

        $buff = trim($buff, '&');

        return $buff;
    }

    /**
     * 构造获取code的url连接.
     *
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     *
     * @return 返回构造好的url
     */
    private function __createOauthUrlForCode()
    {
        $urlObj['appid'] = $this->config['appid'];
        $urlObj['redirect_uri'] = urlencode($this->config['redirect_uri']);
        $urlObj['response_type'] = 'code';
        $urlObj['scope'] = 'snsapi_base';
        $urlObj['state'] = 'STATE'.'#wechat_redirect';
        $bizString = $this->toUrlParams($urlObj);

        return 'https://open.weixin.qq.com/connect/oauth2/authorize?'.$bizString;
    }

    /**
     * 构造获取open和access_toke的url地址
     *
     * @param string $code ，微信跳转带回的code
     *
     * @return 请求的url
     */
    private function __createOauthUrlForOpenid($code)
    {
        $urlObj['appid'] = $this->config['appid'];
        $urlObj['secret'] = $this->config['secret'];
        $urlObj['code'] = $code;
        $urlObj['grant_type'] = 'authorization_code';
        $bizString = $this->toUrlParams($urlObj);

        return 'https://api.weixin.qq.com/sns/oauth2/access_token?'.$bizString;
    }
}
