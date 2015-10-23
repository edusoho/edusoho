<?php
namespace Topxia\WebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Topxia\Service\Common\ServiceKernel;

abstract class BaseCommand extends ContainerAwareCommand
{

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

}