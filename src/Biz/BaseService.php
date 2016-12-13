<?php

namespace Biz;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class BaseService extends \Codeages\Biz\Framework\Service\BaseService
{
    protected function createDao($alias)
    {
        return $this->biz->dao($alias);
    }

    protected function getCurrentUser()
    {
        return $this->biz['user'];
    }

    /**
     * @return EventDispatcherInterface
     */
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

    protected function beginTransaction()
    {
        $this->biz['db']->beginTransaction();
    }

    protected function commit()
    {
        $this->biz['db']->commit();
    }

    protected function rollback()
    {
        $this->biz['db']->rollback();
    }

    protected function getLogger()
    {
        return $this->biz['logger'];
    }

    protected function createAccessDeniedException($message = '')
    {
        return new AccessDeniedException($message);
    }

    protected function createInvalidArgumentException($message = '')
    {
        return new InvalidArgumentException($message);
    }

    protected function createNotFoundException($message = '')
    {
        return new NotFoundException($message);
    }

    protected function createServiceException($message = '')
    {
        return new ServiceException($message);
    }
}
