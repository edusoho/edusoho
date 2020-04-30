<?php

namespace Biz\Plumber\Queue;

interface BaseQueue
{
    public function putJob($id, $topic, $message = null, $options = []);
}
