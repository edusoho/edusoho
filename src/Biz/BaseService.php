<?php

namespace Biz;

use Monolog\Logger;
use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Event\Event;
use Topxia\Service\Common\ServiceKernel;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class BaseService extends \Codeages\Biz\Framework\Service\BaseService
{
    private $lock = null;

    /**
     * @param  $alias
     *
     * @return GeneralDaoInterface
     */
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

    /**
     * @param  $alias
     *
     * @return BaseService
     */
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
     * @param int $code
     * @return AccessDeniedException
     */
    protected function createAccessDeniedException($message = '', $code = 403)
    {
        return new AccessDeniedException($message, $code);
    }

    /**
     * @param string $message
     * @param int $code
     * @return InvalidArgumentException
     */
    protected function createInvalidArgumentException($message = '', $code = 403)
    {
        return new InvalidArgumentException($message, $code);
    }

    /**
     * @param string $message
     * @param int $code
     * @return NotFoundException
     */
    protected function createNotFoundException($message = '', $code = 404)
    {
        return new NotFoundException($message, $code);
    }

    /**
     * @param string $message
     * @param int $code
     * @return ServiceException
     */
    protected function createServiceException($message = '', $code = 404)
    {
        return new ServiceException($message, $code);
    }

    protected function fillOrgId($fields)
    {
        $magic = $this->biz->service('System:SettingService')->get('magic');
        if (isset($magic['enable_org']) && $magic['enable_org']) {
            if (!empty($fields['orgCode'])) {
                $org = ServiceKernel::instance()->createService('Org:OrgService')->getOrgByOrgCode($fields['orgCode']);
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
}
