<?php

namespace Biz\WeChat\Service;

interface WeChatAppService
{
    /**
     * 获取小程序插件状态
     *
     * @return
     * array(
     *  'purchased' => 是否已购买,
     *  'installed' => 是否已安装
     * )
     */
    public function getWeChatAppStatus();
}
