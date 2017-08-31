<?php
namespace Codeages\Biz\Framework\Queue;
use Codeages\Biz\Framework\Context\Biz;

interface Job
{
    const FINISHED = 0;

    const FAILED = 1;

    const FAILED_RETRY = 2;

    const PRI_HIGHEST = 1;

    const PRI_HIGH = 100;

    const PRI_DEFAULT = 1000;

    const PRI_LOW = 2000;

    const PRI_LOWEST = 10000;

    public function execute();

    public function getId();

    public function setId($id);

    public function getBody();

    public function setBody($body);

    public function getMetadata($key = null, $default = null);

    public function setMetadata($spec = null, $value = null);

    public function getQueueName();

    public function setBiz(Biz $biz);
}
