<?php

namespace Biz\Callback\Service;

interface CallbackService
{
    public function notify($type, $queryParams = array());

    public function getCallbackType($type);
}
