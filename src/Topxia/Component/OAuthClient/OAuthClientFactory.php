<?php
namespace Topxia\Component\OAuthClient;

use Topxia\Service\Common\ServiceKernel;

use \InvalidArgumentException;

class OAuthClientFactory
{
	/**
	 * 创建OAuthClient实例
	 * 
	 * @param  string $type   	Client的类型
	 * @param  array $config 	必需包含key, secret两个参数
	 * @return AbstractOauthClient
	 */
    public static function create($type, array $config)
    {
    	if (!array_key_exists('key', $config) or !array_key_exists('secret', $config)) {
    		throw new InvalidArgumentException('参数$config中，必需包含key, secret两个为key的值');
    	}

        $clients = self::clients();

        if (!array_key_exists($type, $clients)) {
            throw new InvalidArgumentException("参数{$type}不正确");
        }

        $class = $clients[$type]['class'];

        return new $class($config);
    }

    public static function clients()
    {
        $clients = array(
            'weibo' => array(
                'name' => '微博帐号',
                'class' => 'Topxia\Component\OAuthClient\WeiboOAuthClient',
                'icon_class' => 'social-icon social-icon-weibo',
                'icon_img' => '',
                'large_icon_img' => 'assets/img/social/weibo.png',
                'key_setting_label' => 'App Key',
                'secret_setting_label' => 'App Secret',
                'apply_url' => 'http://open.weibo.com/authentication/'
            ),
            'qq' => array(
                'name' => 'QQ帐号',
                'class' => 'Topxia\Component\OAuthClient\QqOAuthClient',
                'icon_class' => 'social-icon social-icon-qq',
                'icon_img' => '',
                'large_icon_img' => 'assets/img/social/qq.png',
                'key_setting_label' => 'App ID',
                'secret_setting_label' => 'App Key',
                'apply_url' => 'http://wiki.open.qq.com/wiki/%E3%80%90QQ%E7%99%BB%E5%BD%95%E3%80%91%E7%BD%91%E7%AB%99%E6%8E%A5%E5%85%A5#2._QQ.E7.99.BB.E5.BD.95'

            ),
            'renren' => array(
                'name' => '人人帐号',
                'class' => 'Topxia\Component\OAuthClient\RenrenOAuthClient',
                'icon_class' => 'social-icon social-icon-renren',
                'icon_img' => '',
                'large_icon_img' => 'assets/img/social/renren.gif',
                'key_setting_label' => 'App Key',
                'secret_setting_label' => 'App Secret',
                'apply_url' => 'http://wiki.dev.renren.com/wiki/WEB%E7%BD%91%E7%AB%99%E5%B8%90%E5%8F%B7%E7%99%BB%E5%BD%95%E5%85%A5%E9%97%A8%E6%95%99%E7%A8%8B'
            ),
        );

        $kernel = ServiceKernel::instance();
        if ($kernel->hasParameter('oauth2_clients')) {
            $extras = $kernel->getParameter('oauth2_clients');
            $clients = array_merge($clients, $extras);
        }

        return $clients;
    }
}