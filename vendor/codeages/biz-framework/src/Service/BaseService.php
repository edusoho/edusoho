<?php

namespace Codeages\Biz\Framework\Service;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

abstract class BaseService
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    protected function dispatcher()
    {
        return $this->biz['dispatcher'];
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

    protected function dispatch($eventName, $subject, $arguments = array())
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        return $this->dispatcher()->dispatch($eventName, $event);
    }

    /**
     * @param string $message
     *
     * @return AccessDeniedException
     */
    protected function createAccessDeniedException($message = '')
    {
        return new AccessDeniedException($message);
    }

    /**
     * @param string $message
     *
     * @return InvalidArgumentException
     */
    protected function createInvalidArgumentException($message = '')
    {
        return new InvalidArgumentException($message);
    }

    /**
     * @param string $message
     *
     * @return NotFoundException
     */
    protected function createNotFoundException($message = '')
    {
        return new NotFoundException($message);
    }

    /**
     * @param string $message
     *
     * @return ServiceException
     */
    protected function createServiceException($message = '', $code = 0)
    {
        return new ServiceException($message, $code);
    }

    /**
     * @return Validator
     */
    protected function getValidator()
    {
        return $this->biz['validator'];
    }
}
