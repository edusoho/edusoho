<?php

namespace Codeages\Biz\Framework\Queue;

interface Queue
{
    public function push($queue, array $body, array $options = array());

    public function pushDelay($queue, array $body, $delay, array $options = array());

    public function pop($queue = null, $timeout = 0);
}
