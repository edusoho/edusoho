<?php

namespace Codeages\Plumber\Queue;

interface TopicInterface
{
    public function reserveJob($blocking = false, $timeout = 2);

    public function putJob(Job $job);

    public function buryJob(Job $job);

    public function finishJob(Job $job);
}
