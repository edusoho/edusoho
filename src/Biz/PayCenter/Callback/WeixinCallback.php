<?php 

namespace Biz\PayCenter\Callback;

use Biz\Callback\Callback;

class WeixinCallback extends Callback
{
    public $forwardController = 'AppBundle:Callback/Weixin';

    public function notify($queryParams)
    {

    }
}