<?php

namespace Biz;

use Biz\Org\OrgException;
use Monolog\Logger;
use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Event\Event;
use Topxia\Service\Common\ServiceKernel;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use AppBundle\Common\Exception\AbstractException;

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

    protected function createNewException($e)
    {
        if ($e instanceof AbstractException) {
            throw $e;
        }

        throw new \Exception();
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
                    $this->createNewException(OrgException::NOTFOUND_ORG());
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

    /**
     * @param $code
     *
     * @return array
     *               根据给定的权限，获取匹配的新|老后台的权限
     */
    protected function getMarriedPermissions($code)
    {
        $rolePermissionsYml = $this->biz['role.get_permissions_yml'];
        $allPermissions = array_merge($rolePermissionsYml['adminV2'], $rolePermissionsYml['admin']);

        return !empty($allPermissions[$code]) ? $allPermissions[$code] : array();
    }
}
