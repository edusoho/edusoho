<?php

namespace Biz\WrongBook\Pool;

use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractPool
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function getPoolTarget($report);

    abstract public function prepareSceneIds($poolId, $conditions);

    abstract public function buildConditions($pool, $conditions);

    /**
     * @return Biz
     */
    final public function getBiz()
    {
        return $this->biz;
    }

    /**
     * @return CurrentUser
     */
    protected function getCurrentUser()
    {
        $biz = $this->getBiz();

        return $biz['user'];
    }
}
