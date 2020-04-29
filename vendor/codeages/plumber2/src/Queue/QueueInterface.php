<?php

namespace Codeages\Plumber\Queue;

interface QueueInterface
{
    /**
     * @param string $name
     *
     * @return TopicInterface
     */
    public function listenTopic(string $name): TopicInterface;

    public function clearTopic(string $name);

    public function stats();
}
