<?php

namespace Codeages\Plumber\Queue;

class Job
{
    /**
     * Job ID
     *
     * @var int
     */
    private $id;

    /**
     * Job Body
     *
     * @var mixed
     */
    private $body;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var int
     */
    private $delay;

    /**
     * @var int
     */
    private $ttr;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * @param int $delay
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;
    }

    /**
     * @return int
     */
    public function getTtr()
    {
        return $this->ttr;
    }

    /**
     * @param int $ttr
     */
    public function setTtr($ttr)
    {
        $this->ttr = $ttr;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}
