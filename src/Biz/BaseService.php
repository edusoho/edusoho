<?php

namespace Biz;


use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

class BaseService extends \Codeages\Biz\Framework\Service\BaseService
{
    /**
     * @param $alias
     * @return GeneralDaoInterface
     */
    protected function createDao($alias)
    {
        return $this->biz->dao($alias);
    }

    protected function getCurrentUser()
    {
        return $this->biz['user'];
    }

}