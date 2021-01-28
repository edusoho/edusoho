<?php

namespace Codeages\PluginBundle\Event;

use Codeages\Biz\Framework\Context\Biz;

class EventSubscriber
{
    /**
     * @var Biz
     */
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @return Biz
     */
    public function getBiz()
    {
        return $this->biz;
    }
}
