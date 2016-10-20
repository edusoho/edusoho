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

    /**
     * @var mixed
     */
    protected $subject;

    /**
     * @var array
     */
    protected $arguments = array();

    public abstract function trigger();

    public final function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function setArguments(array $arguments=array())
    {
        $this->arguments = $arguments;
        return $this;
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
    final protected function getBiz()
    {
        return $this->biz;
    }

    final protected function getSubject()
    {
        return $this->subject;
    }

    final protected function getArguments()
    {
        return $this->arguments;
    }
}
