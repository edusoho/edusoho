<?php

namespace Biz;

use Monolog\Logger;
use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Event\Event;
use Topxia\Service\Common\ServiceKernel;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class BaseService extends \Codeages\Biz\Framework\Service\BaseService
{
    private $lock = null;

    protected function createDao($alias)
    {
        return $this->biz->dao($alias);
    }

    /**
     * @return CurrentUser
     */
    public function getCurrentUser()
    {
        return $this->biz['user'];
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    /**
     * @return EventDispatcherInterface
     */
    private function getDispatcher()
    {
        return $this->biz['dispatcher'];
    }

    /**
     * @param string      $eventName
     * @param Event|mixed $subject
     *
     * @return Event
     */
    protected function dispatchEvent($eventName, $subject, $arguments = array())
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
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

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->biz['logger'];
    }

    /**
     * @param string $message
     *
     * @return AccessDeniedException
     */
    protected function createAccessDeniedException($message = 'Access Denied')
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

    protected function fillOrgId($fields)
    {
        $magic = $this->biz->service('System:SettingService')->get('magic');
        if (isset($magic['enable_org']) && $magic['enable_org']) {
            if (!empty($fields['orgCode'])) {
                $org = $this->createService('Org:OrgService')->getOrgByOrgCode($fields['orgCode']);
                if (empty($org)) {
                    throw $this->createNotFoundException('组织机构不存在,更新失败');
                }
                $fields['orgId'] = $org['id'];
                $fields['orgCode'] = $org['orgCode'];
            } else {
                unset($fields['orgCode']);
            }
        } else {
            unset($fields['orgCode']);
        }

        return $fields;
    }

    protected function purifyHtml($html, $trusted = false)
    {
        $htmlHelper = $this->biz['html_helper'];

        return $htmlHelper->purify($html, $trusted);
    }

    protected function getLock()
    {
        if (!$this->lock) {
            $this->lock = new Lock($this->biz);
        }

        return $this->lock;
    }

    protected function trans($message, $arguments = array(), $domain = null, $locale = null)
    {
        return ServiceKernel::instance()->trans($message, $arguments, $domain, $locale);
    }
}
