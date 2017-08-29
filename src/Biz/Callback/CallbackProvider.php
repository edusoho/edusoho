<?php 

namespace Biz\Callback;

use Codeages\Biz\Framework\Context\Biz;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Biz\PayCenter\Callback\WeixinCallback;

Class CallbackProvider implements ServiceProviderInterface
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