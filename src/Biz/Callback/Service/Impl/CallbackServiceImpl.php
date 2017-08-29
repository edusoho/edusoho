<?php 

namespace Biz\Callback\Service\Impl;

use Biz\BaseService;
use Biz\Callback\Service\CallbackService;

class CallbackServiceImpl extends BaseService implements CallbackService
{
    public function notify($type, $queryParams = array())
    {
        $type = $queryParams['type'];
        $targetCallback = $this->getCallbackType($type);

        $targetCallback->notify($queryParams);
    }

    public function getCallbackType($type)
    {
        if (!empty($this->biz["callback_notify.{$type}"])) {
            return $this->biz["callback_notify.{$type}"];
        } else {
            throw $this->createServiceException("Callback notify type({$type}) not found");
        }
    }
}