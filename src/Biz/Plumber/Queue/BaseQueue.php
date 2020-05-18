<?php

namespace Biz\Plumber\Queue;

interface BaseQueue
{
    public function putJob($id, $worker, $messages = [], $options = []);
}
