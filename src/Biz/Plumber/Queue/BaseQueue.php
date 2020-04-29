<?php

namespace Biz\Plumber\Queue;

interface BaseQueue
{
    public function putJob($topic, $message, $options = []);
}
