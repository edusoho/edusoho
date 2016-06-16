<?php
namespace Topxia\Service\Common;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Topxia\Service\Common\ServiceException;
use Topxia\Service\Common\NotFoundException;
use Topxia\Service\Util\HTMLPurifierFactory;
use Topxia\Service\Common\AccessDeniedException;

abstract class BaseService
{
    private $logger = null;

    protected function createService($name)
    {
        return $this->getKernel()->createService($name);
    }

    protected function createDao($name)
    {
        return $this->getKernel()->createDao($name);
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }

    public function getCurrentUser()
    {
        return $this->getKernel()->getCurrentUser();
    }

    public function getEnvVariable($key = null)
    {
        return $this->getKernel()->getEnvVariable($key);
    }

    public function getDispatcher()
    {
        return ServiceKernel::dispatcher();
    }

    protected function dispatchEvent($eventName, $subject)
    {
        if ($subject instanceof ServiceEvent) {
            $event = $subject;
        } else {
            $event = new ServiceEvent($subject);
        }

        return $this->getDispatcher()->dispatch($eventName, $event);
    }

    protected function purifyHtml($html, $trusted = false)
    {
        if (empty($html)) {
            return '';
        }

        $config = array(
            'cacheDir' => $this->getKernel()->getParameter('kernel.cache_dir').'/htmlpurifier'
        );

        $factory  = new HTMLPurifierFactory($config);
        $purifier = $factory->create($trusted);

        return $purifier->purify($html);
    }

    protected function createServiceException($message = 'Service Exception', $code = 0)
    {
        return new ServiceException($message, $code);
    }

    protected function createAccessDeniedException($message = 'Access Denied', $code = 0)
    {
        return new AccessDeniedException($message, null, $code);
    }

    protected function createNotFoundException($message = 'Not Found', $code = 0)
    {
        return new NotFoundException($message, $code);
    }

    protected function fillOrgId($fields)
    {
        $magic = $this->createService('System.SettingService')->get('magic');

        if (isset($magic['enable_org']) && $magic['enable_org']) {
            if (!empty($fields['orgCode'])) {
                $org = $this->createService('Org:Org.OrgService')->getOrgByOrgCode($fields['orgCode']);
                if (empty($org)) {
                    throw $this->createServiceException("组织机构{$fields['orgCode']}不存在,更新失败");
                }
                $fields['orgId']   = $org['id'];
                $fields['orgCode'] = $org['orgCode'];
            } else {
                unset($fields['orgCode']);
            }
        } else {
            unset($fields['orgCode']);
        }
        return $fields;
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
}
