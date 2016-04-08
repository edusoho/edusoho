<?php
namespace Topxia\WebBundle\Command;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class BaseCommand extends ContainerAwareCommand
{
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function trans($message, $arguments = array(), $domain = null, $locale = null)
    {
        $translator = $this->getContainer()->get('translator');

        return $translator->trans($message, $arguments, $domain, $locale); // works fine! :)
    }

}
