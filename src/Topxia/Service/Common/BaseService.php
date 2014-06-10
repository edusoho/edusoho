<?php
namespace Topxia\Service\Common;

use Topxia\Service\Common\ServiceException;
use Topxia\Service\Common\NotFoundException;
use Topxia\Service\Common\AccessDeniedException;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Util\HTMLPurifierFactory;

abstract class BaseService
{

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

    public function getDispatcher()
    {
        return ServiceKernel::dispatcher();
    }

    protected function purifyHtml($html, $trusted = false)
    {
        if (empty($html)) {
            return '';
        }

        $config = array(
            'cacheDir' => $this->getKernel()->getParameter('kernel.cache_dir') .  '/htmlpurifier'
        );

        $factory = new HTMLPurifierFactory($config);
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

}