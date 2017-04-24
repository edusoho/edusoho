<?php

namespace Biz\Accessor;

use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Context\Biz;

abstract class AccessorAdapter implements AccessorInterface
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    abstract public function access($bean);

    protected function buildResult($code, $params = array())
    {
        //todo translate message with params
        return $this->biz['accessor.join_course'][$code];
    }

    /**
     * @return CurrentUser
     */
    protected function getCurrentUser()
    {
        return $this->biz['user'];
    }
}
