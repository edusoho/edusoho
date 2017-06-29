<?php

namespace AppBundle\Command;

use Biz\Role\Util\PermissionBuilder;
use Topxia\Service\Common\ServiceKernel;
use Biz\User\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class BaseCommand extends ContainerAwareCommand
{
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function initServiceKernel()
    {
        $_SERVER['HTTP_HOST'] = '127.0.0.1';
        $serviceKernel = ServiceKernel::create('dev', false);
        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());
        $serviceKernel->setBiz($this->getBiz());

        $currentUser = new CurrentUser();
        $systemUser = $this->getUserService()->getUserByType('system');
        $systemUser['currentIp'] = '127.0.0.1';
        $currentUser->fromArray($systemUser);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $serviceKernel->setCurrentUser($currentUser);
    }

    protected function getBiz()
    {
        return $this->getContainer()->get('biz');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function createService($alias)
    {
        $biz = $this->getBiz();

        return $biz->service($alias);
    }

    protected function trans($message, $arguments = array(), $domain = null, $locale = null)
    {
        $translator = $this->getContainer()->get('translator');

        return $translator->trans($message, $arguments, $domain, $locale); // works fine! :)
    }
}
