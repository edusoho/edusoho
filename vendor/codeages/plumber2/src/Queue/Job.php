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
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return int
     */
    public function getDelay(): int
    {
        return $this->delay;
    }

    /**
     * @param int $delay
     */
    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }

    /**
     * @return int
     */
    public function getTtr(): int
    {
        return $this->ttr;
    }

    /**
     * @param int $ttr
     */
    public function setTtr(int $ttr): void
    {
        $this->ttr = $ttr;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
