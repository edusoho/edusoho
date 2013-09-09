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

    protected function getContainer()
    {
        return $this->getKernel()->getContainer();
    }

    public function getCurrentUser()
    {
        if (!$this->getContainer()->has('security.context')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        $token = $this->getContainer()->get('security.context')->getToken();

        if ( empty($token) or !is_object($user = $token->getUser())) {
            $user = new CurrentUser();
            $user->fromArray(array(
                'id' => 0,
                'nickname' => '游客',
                'currentIp' =>  $this->getContainer()->get('request')->getClientIp()
            ));
        }

        return $user;
    }

    protected function getRequest()
    {
        return $this->getContainer()->get('request');
    }

    protected function purifyHtml($html)
    {
        if (empty($html)) {
            return '';
        }

        $config = array(
            'cacheDir' => $this->getContainer()->getParameter('kernel.cache_dir') .  '/htmlpurifier'
        );

        $factory = new HTMLPurifierFactory($config);
        $purifier = $factory->create();

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
