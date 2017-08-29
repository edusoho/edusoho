<?php

namespace Biz\PayCenter\Callback;

use Biz\Callback\Callback;

class WxpayCallback extends Callback
{
    public $forwardController = 'AppBundle:Callback/Wxpay';

    public function notify($params)
    {
    }
}
