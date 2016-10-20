<?php

namespace Biz\Activity\Event;

use Codeages\Biz\Framework\Context\Biz;

abstract class Event
{
    protected $name;

    /**
     * @var Biz
     */
    private $biz;

    public abstract function trigger($activity, $data);

    public final function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @param $name
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Biz
     */
    public function getBiz()
    {
        return $this->biz;
    }
}
