<?php

namespace Biz;

use AppBundle\Common\Exception\AbstractException;
use Biz\Org\OrgException;
use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;

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
     * @param string      $eventName
     * @param Event|mixed $subject
     *
     * @return Event
     */
    protected function dispatchEvent($eventName, $subject, $arguments = [])
    {
        return $this->dispatch($eventName, $subject, $arguments);
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
        return parent::createAccessDeniedException($message);
    }

    protected function createNewException($e)
    {
        if ($e instanceof AbstractException) {
            throw $e;
        }

        throw new \Exception();
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
        return $this->biz['html_helper']->purify($html, $trusted);
    }

    protected function getLock()
    {
        if (!$this->lock) {
            $this->lock = new Lock($this->biz);
        }

        return $this->lock;
    }

    protected function trans($message, $arguments = [], $domain = null, $locale = null)
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

        return !empty($allPermissions[$code]) ? $allPermissions[$code] : [];
    }

    /**
     * @param $pluginCode
     *
     * @return bool
     */
    protected function isPluginInstalled($pluginCode)
    {
        global $kernel;

        return $kernel->getPluginConfigurationManager()->isPluginInstalled($pluginCode);
    }
}
