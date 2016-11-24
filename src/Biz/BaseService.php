<?php

namespace Biz;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class BaseService extends \Codeages\Biz\Framework\Service\BaseService
{
    /**
     * @param  $alias
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

    protected function getDispatcher()
    {
        return $this->biz['dispatcher'];
    }

    protected function dispatchEvent($eventName, $subject)
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject);
        }

        return $this->getDispatcher()->dispatch($eventName, $event);
    }

    protected function createNotFoundException($resourceType, $resourceId)
    {
        return new NotFoundException($resourceType, $resourceId);
    }

    protected function createAccessDeniedException($message = '')
    {
        return new AccessDeniedException($message);
    }

    protected function createInvalidArgumentException($message = '')
    {
        return new InvalidArgumentException($message);
    }

    protected function createServiceException($message = '')
    {
        return new ServiceException($message);
    }
}
