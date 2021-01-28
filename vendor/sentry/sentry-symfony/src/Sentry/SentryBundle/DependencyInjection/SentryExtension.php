<?php

namespace Sentry\SentryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SentryExtension extends Extension
{
    /**
     * {@inheritDoc}
     *
     * @throws InvalidConfigurationException
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        foreach ($config as $key => $value) {
            $container->setParameter('sentry.' . $key, $value);
        }

        foreach ($config['listener_priorities'] as $key => $priority) {
            $container->setParameter('sentry.listener_priorities.' . $key, $priority);
        }

        // TODO Can be removed when deprecated config options are removed
        $this->checkConfigurationOnForInvalidSettings($config, $container);
    }

    /**
     * Synchronises old deprecated and new configuration values to have the same value.
     * An exception will be thrown if new and deprecated options are both set to non default values.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     *
     * @throws InvalidConfigurationException
     */
    private function checkConfigurationOnForInvalidSettings(array $config, ContainerBuilder $container)
    {
        foreach ($this->getDeprecatedOptionsWithDefaults() as $option => $default) {
            // old option is used
            if ($config[$option] !== $default) {
                $deprecationMessage = sprintf(
                    'The usage of sentry.%s is deprecated since version 0.8.3 and will be removed in 1.0. Use sentry.options.%s instead.',
                    $option,
                    $option
                );
                @trigger_error($deprecationMessage, E_USER_DEPRECATED);
            }

            // both are used, check if there are issues
            if (
                $config[$option] !== $default
                && $config['options'][$option] !== $default
                && $config['options'][$option] !== $config[$option]
            ) {
                $message = sprintf(
                    'You are using both the deprecated option sentry.%s and the new sentry.options.%s, but values do not match. Drop the deprecated one or make the values identical.',
                    $option,
                    $option
                );
                throw new InvalidConfigurationException($message);
            }

            // new option is used, overrides old one
            if ($config[$option] === $default && $config['options'][$option] !== $default) {
                $config[$option] = $config['options'][$option];
            }

            $container->setParameter('sentry.' . $option, $config[$option]);
            $container->setParameter('sentry.options.' . $option, $config[$option]);
        }
    }

    /**
     * @return array
     */
    private function getDeprecatedOptionsWithDefaults()
    {
        return array(
            'environment' => '%kernel.environment%',
            'app_path' => '%kernel.root_dir%/..',
            'release' => null,
            'prefixes' => array('%kernel.root_dir%/..'),
            'error_types' => null,
            'excluded_app_paths' => array(
                '%kernel.root_dir%/../vendor',
                '%kernel.root_dir%/../app/cache',
                '%kernel.root_dir%/../var/cache',
            ),
        );
    }
}
