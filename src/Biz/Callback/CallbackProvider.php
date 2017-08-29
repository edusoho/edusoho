<?php

namespace Biz\Callback;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Biz\PayCenter\Callback\WeixinCallback;

class CallbackProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['callback_notify.weixin'] = function ($biz) {
            $callback = new WeixinCallback();
            $callback->setBiz($biz);

            return $callback;
        };
    }
}
