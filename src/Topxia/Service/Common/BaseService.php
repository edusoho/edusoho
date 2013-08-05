<?php
namespace Topxia\Service\Common;

use Topxia\Service\Common\ServiceException;
use Topxia\Service\Common\NotFoundException;
use Topxia\Service\Common\AccessDeniedException;
use Topxia\Service\User\CurrentUser;

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

        if (null === $token = $this->getContainer()->get('security.context')->getToken()) {
            throw new \LogicException('The Authentication token is not available.');
        }

        if (!is_object($user = $token->getUser())) {
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

    protected function getHtmlPurifier() {
        return $this->getContainer()->get('topxia.htmlpurifier');
    }

    protected function getMediaParseService()
    {
        return $this->createService('Util.MediaParseService');
    }

    protected function createServiceException($message = 'Service Exception', $code = 0)
    {
        return new ServiceException($message, $code);
    }

    protected function createAccessDeniedException($message = 'Access Denied', $code = 0)
    {
        return new AccessDeniedException($message, $code);
    }

    protected function createNotFoundException($message = 'Not Found', $code = 0)
    {
        return new NotFoundException($message, $code);
    }

}
