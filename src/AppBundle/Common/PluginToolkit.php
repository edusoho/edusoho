<?php

namespace AppBundle\Common;

use Codeages\PluginBundle\System\PluginConfigurationManager;
use Topxia\Service\Common\ServiceKernel;

class PluginToolkit
{
    public static function isPluginInstalled($code)
    {
        $pluginManager = new PluginConfigurationManager(ServiceKernel::instance()->getParameter('kernel.root_dir'));

        return $pluginManager->isPluginInstalled($code);
    }
}
