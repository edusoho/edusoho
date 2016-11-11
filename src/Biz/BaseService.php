<?php

namespace Biz;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class BaseService extends \Codeages\Biz\Framework\Service\BaseService
{
    /**
     * @param  $alias
     * @return GeneralDaoImpl
     */
    protected function createDao($alias)
    {
        return $this->biz->dao($alias);
    }

    protected function getCurrentUser()
    {
        return $this->biz['user'];
    }

    protected function dispatchEvent($eventName, $subject)
    {
        if ($subject instanceof ServiceEvent) {
            $event = $subject;
        } else {
            $event = new ServiceEvent($subject);
        }

        return ServiceKernel::dispatcher()->dispatch($eventName, $event);
    }
}
