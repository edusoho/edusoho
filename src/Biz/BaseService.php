<?php

namespace Biz;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Common\ServiceException;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Topxia\Service\Common\AccessDeniedException;
use Topxia\Service\Common\ResourceNotFoundException;

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

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    /**
     * @deprecated this is deprecated and will be removed. Please use use `throw new Topxia\Common\Exception\XXXException(...)` instead.
     */
    protected function createServiceException($message = 'Service Exception', $code = 0)
    {
        return new ServiceException($message, $code);
    }

    /**
     * @deprecated this is deprecated and will be removed. Please use use `throw new Topxia\Common\Exception\XXXException(...)` instead.
     */
    protected function createAccessDeniedException($message = 'Access Denied', $code = 0)
    {
        return new AccessDeniedException($message, null, $code);
    }

    /**
     * @deprecated this is deprecated and will be removed. Please use use `throw new Topxia\Common\Exception\XXXException(...)` instead.
     */
    protected function createNotFoundException($message = 'Not Found', $code = 0)
    {
        return new ResourceNotFoundException($message, $code);
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
