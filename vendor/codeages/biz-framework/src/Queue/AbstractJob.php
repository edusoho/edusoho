<?php

namespace Codeages\Biz\Framework\Queue;

use Codeages\Biz\Framework\Context\Biz;
use Traversable;

abstract class AbstractJob implements Job
{
    protected $id;

    protected $body;

    protected $metadata = array();

    protected $biz;

    public function __construct($body = null, array $metadata = array())
    {
        $this->setBody($body);
        $this->setMetadata($metadata);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getMetadata($key = null, $default = null)
    {
        if (null === $key) {
            return $this->metadata;
        }

        if (!is_scalar($key)) {
            throw new \InvalidArgumentException('Non-scalar argument provided for key');
        }

        if (array_key_exists($key, $this->metadata)) {
            return $this->metadata[$key];
        }

        return $default;
    }

    public function setMetadata($spec = null, $value = null)
    {
        if (is_scalar($spec)) {
            $this->metadata[$spec] = $value;

            return $this;
        }
        if (!is_array($spec) && !$spec instanceof Traversable) {
            throw new InvalidArgumentException(sprintf(
                'Expected a string, array, or Traversable argument in first position; received "%s"',
                (is_object($spec) ? get_class($spec) : gettype($spec))
            ));
        }
        foreach ($spec as $key => $value) {
            $this->metadata[$key] = $value;
        }

        return $this;
    }

    public function setBiz(Biz $biz)
    {
        $this->biz = $biz;
    }
}
