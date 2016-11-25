<?php

namespace Biz;


use Codeages\Biz\Framework\Dao\GeneralDaoInterface;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Topxia\Service\Common\Lock;
use Topxia\Service\User\CurrentUser;

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

    /**
     * @return CurrentUser
     */
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

    protected function createNotFoundException($message = '')
    {
        return new NotFoundException($message);
    }

    protected function createServiceException($message = '')
    {
        return new ServiceException($message);
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

    protected function getLock()
    {
        if (!$this->lock) {
            $this->lock = new Lock();
        }

        return $this->lock;
    }

    protected function getLogger($name)
    {
        if ($this->logger) {
            return $this->logger;
        }

        $this->logger = new Logger($name);
        $this->logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/service.log', Logger::DEBUG));

        return $this->logger;
    }

    protected function createAccessDeniedException($message = '') 
    {
        return new AccessDeniedException($message);
    }

    protected function createInvalidArgumentException($message = '') 
    {
        return new InvalidArgumentException($message);
    }
}