<?php


namespace Codeages\PluginBundle\Event;


use Codeages\Biz\Framework\Context\Biz;

class Subscribers
{
    /**
     * @var Biz
     */
    private $biz;
    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function addSubscriber($subscriberClass)
    {
        $this->biz['subscribers'][] = $subscriberClass;
    }
}