<?php

namespace ESCloud\SDKDemo\Sdk;

use ESCloud\SDK\ESCloudSDK;
use ESCloud\SDK\Exception\SDKException;

class Sdk
{
    /**
     * Sdk constructor.
     * @throws SDKException
     */
    public static function init()
    {
        return new ESCloudSDK(array(
            'access_key' => 'vqF0cR7TIpbIh1mJxS55vn6QgILGHCzF', // 必需，请替换成自己的
            'secret_key' => 'wyOV9rew98ClmpuklnT1Y80omjc7ZLel', // 必需，请替换成自己的
            'service' => array(     // 可选，各个服务的配置项
                'resource' => array(    // 每个服务，都有自己的必需的配置项，如需调用则必需配置该服务的配置项
                    'host' => 'resource-service.local.cg-dev.cn',//当前是测试域名，正式环境，直接去掉即可
                )
            )
        ));
    }
}
