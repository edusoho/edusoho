<?php
namespace Codeages\Biz\Framework\Queue\Driver;
use Pimple\Container;
use Codeages\Biz\Framework\Queue\Job;
use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractQueue implements Queue
{
    /**
     * @var string
     */
    protected $name;

    protected $biz;

    protected $options;

    public function __construct($name, Biz $biz, array $options = array())
    {
        $this->name = $name;
        $this->biz = $biz;
        $this->options = array_merge(array(
            'job_timeout' => 60,
        ), $options);
    }

    public function getName()
    {
        return $this->name;
    }
}