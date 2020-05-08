<?php

namespace Codeages\Plumber\Queue;

interface QueueInterface
{
    /**
     * @param string $name
     *
     * @return TopicInterface
     */
    public function listenTopic($name);

    public function clearTopic($name);

    public function stats();
}
