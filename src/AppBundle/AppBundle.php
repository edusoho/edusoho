<?php

namespace AppBundle;

use AppBundle\DependencyInjection\Compiler\ActivityRuntimeContainerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use AppBundle\DependencyInjection\Compiler\ExtensionPass;
use AppBundle\Common\ExtensionalBundle;

class AppBundle extends ExtensionalBundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExtensionPass());
        $container->addCompilerPass(new ActivityRuntimeContainerPass());
    }

    public function boot()
    {
        parent::boot();

        $biz = $this->container->get('biz');

        $activityConfigManager = $this->container->get('activity_config_manager');

        $installedActivities = $activityConfigManager->getInstalledActivities();

        foreach ($installedActivities as $installedActivity) {
            $migrationsDir = implode(DIRECTORY_SEPARATOR, array($installedActivity['dir'], 'migrations'));
            if (file_exists($migrationsDir)) {
                $biz['migration.directories'][] = $migrationsDir;
            }
        }
    }

    public function getEnabledExtensions()
    {
        return array('DataTag', 'StatusTemplate', 'DataDict', 'NotificationTemplate');
    }
}
