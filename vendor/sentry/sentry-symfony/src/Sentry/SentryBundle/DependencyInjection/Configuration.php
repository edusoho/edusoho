<?php

namespace Sentry\SentryBundle\DependencyInjection;

use Raven_Compat;
use Sentry\SentryBundle\SentryBundle;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sentry');
        $trimClosure = function ($str) {
            $value = trim($str);
            if ($value === '') {
                return null;
            }

            return $value;
        };

        $rootNode
            ->children()
                ->scalarNode('app_path')
                    ->defaultValue('%kernel.root_dir%/..')
                ->end()
                ->scalarNode('client')
                    ->defaultValue('Sentry\SentryBundle\SentrySymfonyClient')
                ->end()
                ->scalarNode('environment')
                    ->defaultValue('%kernel.environment%')
                ->end()
                ->scalarNode('dsn')
                    ->beforeNormalization()
                        ->ifString()
                        ->then($trimClosure)
                    ->end()
                    ->defaultNull()
                ->end()
                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('logger')->defaultValue('php')->end()
                        ->scalarNode('server')->defaultNull()->end()
                        ->scalarNode('secret_key')->defaultNull()->end()
                        ->scalarNode('public_key')->defaultNull()->end()
                        ->scalarNode('project')->defaultValue(1)->end()
                        ->booleanNode('auto_log_stacks')->defaultFalse()->end()
                        ->scalarNode('name')->defaultValue(Raven_Compat::gethostname())->end()
                        ->scalarNode('site')->defaultNull()->end()
                        ->arrayNode('tags')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('release')->defaultNull()->end()
                        ->scalarNode('environment')->defaultValue('%kernel.environment%')->end()
                        ->scalarNode('sample_rate')->defaultValue(1)->end()
                        ->booleanNode('trace')->defaultTrue()->end()
                        ->scalarNode('timeout')->defaultValue(2)->end()
                        ->scalarNode('message_limit')->defaultValue(\Raven_Client::MESSAGE_LIMIT)->end()
                        ->arrayNode('exclude')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('excluded_exceptions')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('http_proxy')->defaultNull()->end()
                        ->arrayNode('extra')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('curl_method')->defaultValue('sync')->end()
                        ->scalarNode('curl_path')->defaultValue('curl')->end()
                        ->booleanNode('curl_ipv4')->defaultTrue()->end()
                        ->scalarNode('ca_cert')->defaultNull()->end()
                        ->booleanNode('verify_ssl')->defaultTrue()->end()
                        ->scalarNode('curl_ssl_version')->defaultNull()->end()
                        ->scalarNode('trust_x_forwarded_proto')->defaultFalse()->end()
                        ->scalarNode('mb_detect_order')->defaultNull()->end()
                        ->scalarNode('error_types')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('app_path')->defaultValue('%kernel.root_dir%/..')->end()
                        ->arrayNode('excluded_app_paths')
                            ->defaultValue(
                                array(
                                    '%kernel.root_dir%/../vendor',
                                    '%kernel.root_dir%/../app/cache',
                                    '%kernel.root_dir%/../var/cache',
                                )
                            )
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('prefixes')
                            ->defaultValue(array('%kernel.root_dir%/..'))
                            ->prototype('scalar')->end()
                        ->end()
                        ->booleanNode('install_default_breadcrumb_handlers')->defaultTrue()->end()
                        ->booleanNode('install_shutdown_handler')->defaultTrue()->end()
                        ->arrayNode('processors')
                            ->defaultValue(array('Raven_SanitizeDataProcessor'))
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('processorOptions')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('error_types')
                    ->defaultNull()
                ->end()
                ->scalarNode('exception_listener')
                    ->defaultValue('Sentry\SentryBundle\EventListener\ExceptionListener')
                    ->validate()
                    ->ifTrue($this->getExceptionListenerInvalidationClosure())
                        ->thenInvalid('The "sentry.exception_listener" parameter should be a FQCN of a class implementing the SentryExceptionListenerInterface interface')
                    ->end()
                ->end()
                ->arrayNode('skip_capture')
                    ->prototype('scalar')->end()
                    ->defaultValue(array('Symfony\Component\HttpKernel\Exception\HttpExceptionInterface'))
                ->end()
                ->scalarNode('release')
                    ->defaultNull()
                ->end()
                ->arrayNode('prefixes')
                    ->prototype('scalar')->end()
                    ->defaultValue(array('%kernel.root_dir%/..'))
                ->end()
                ->arrayNode('excluded_app_paths')
                    ->prototype('scalar')->end()
                    ->defaultValue(array(
                        '%kernel.root_dir%/../vendor',
                        '%kernel.root_dir%/../app/cache',
                        '%kernel.root_dir%/../var/cache',
                    ))
                ->end()
                ->arrayNode('listener_priorities')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('request')->defaultValue(0)->end()
                        ->scalarNode('kernel_exception')->defaultValue(0)->end()
                        ->scalarNode('console_exception')->defaultValue(0)->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @return \Closure
     */
    private function getExceptionListenerInvalidationClosure()
    {
        return function ($value) {
            $implements = class_implements($value);
            if ($implements === false) {
                return true;
            }
            return !in_array('Sentry\SentryBundle\EventListener\SentryExceptionListenerInterface', $implements, true);
        };
    }
}
