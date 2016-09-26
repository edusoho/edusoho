<?php
namespace Topxia\WebBundle\Command;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class BaseCommand extends ContainerAwareCommand
{
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function initServiceKernel()
    {
        $serviceKernel = ServiceKernel::create('dev', false);
        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());

        $serviceKernel->setConnection($this->getContainer()->get('database_connection'));

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id'        => 0,
            'nickname'  => '游客',
            'currentIp' => '127.0.0.1',
            'roles'     => array()
        ));
        $serviceKernel->setCurrentUser($currentUser);
    }

    protected function trans($message, $arguments = array(), $domain = null, $locale = null)
    {
        $translator = $this->getContainer()->get('translator');

        return $translator->trans($message, $arguments, $domain, $locale); // works fine! :)
    }

}
