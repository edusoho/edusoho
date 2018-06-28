<?php

namespace Biz\WeChat\Service;

interface WeChatAppService
{
    /**
     * @return 小程序助手插件的状态
     *                                        array(
     *                                        'purchased' => 是否已购买,
     *                                        'installed' => 是否已安装
     *                                        )
     */
    public function getWeChatAppStatus();
}
