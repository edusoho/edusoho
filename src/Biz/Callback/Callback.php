<?php 

namespace Biz\Callback;

use Codeages\Biz\Framework\Context\BizAware;

abstract class Callback extends BizAware
{
    public $forwardController;

    abstract public function notify($queryParams);
}