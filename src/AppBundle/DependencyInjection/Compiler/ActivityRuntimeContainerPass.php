<?php

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Finder\Finder;

class ActivityRuntimeContainerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->addTranslationsFiles($container);
    }

    private function addTranslationsFiles(ContainerBuilder $container)
    {
        $activityConfigManager = $container->get('activity_config_manager');

        $installedActivities = $activityConfigManager->getInstalledActivities();

        $translationFiles = array();
        foreach ($installedActivities as $installedActivity) {
            $translationDir = implode(DIRECTORY_SEPARATOR, array($installedActivity['dir'], 'resources', 'translations'));

            if (!file_exists($translationDir)) {
                continue;
            }

            $transFiles = Finder::create()->files()->in($translationDir);

            foreach ($transFiles as $file) {
                /* @var \Symfony\Component\Finder\SplFileInfo $file */
                list(, $locale) = explode('.', $file->getBasename(), 3);
                if (!isset($translationFiles[$locale])) {
                    $translationFiles[$locale] = array();
                }

                $translationFiles[$locale][] = $file->getRealPath();
            }
        }
        $translator = $container->findDefinition('translator.default');

        $options = $translator->getArgument(4);
        $options['resource_files'] = array_merge_recursive($options['resource_files'], $translationFiles);
        $translator->replaceArgument(4, $options);
    }
}
