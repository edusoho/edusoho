<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SwiftmailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class Configuration implements ConfigurationInterface
{
    private $debug;

    /**
     * Constructor.
     *
     * @param Boolean $debug The kernel.debug value
     */
    public function __construct($debug)
    {
        $this->debug = (Boolean) $debug;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('swiftmailer');

        $rootNode
            ->beforeNormalization()
                ->ifTrue(function ($v) { return is_array($v) && !array_key_exists('mailers', $v) && !array_key_exists('mailer', $v); })
                ->then(function ($v) {
                    $mailer = array();
                    foreach ($v as $key => $value) {
                        if ('default_mailer' == $key) {
                            continue;
                        }
                        $mailer[$key] = $v[$key];
                        unset($v[$key]);
                    }
                    $v['default_mailer'] = isset($v['default_mailer']) ? (string) $v['default_mailer'] : 'default';
                    $v['mailers'] = array($v['default_mailer'] => $mailer);

                    return $v;
                })
            ->end()
            ->children()
                ->scalarNode('default_mailer')->end()
                ->append($this->getMailersNode())
            ->end()
            ->fixXmlConfig('mailer')
        ;

        return $treeBuilder;
    }

    /**
     * Return the mailers node
     *
     * @return ArrayNodeDefinition
     */
    private function getMailersNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('mailers');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
                ->prototype('array')
            ->children()
                ->scalarNode('transport')->defaultValue('smtp')->end()
                ->scalarNode('username')->defaultNull()->end()
                ->scalarNode('password')->defaultNull()->end()
                ->scalarNode('host')->defaultValue('localhost')->end()
                ->scalarNode('port')->defaultNull()->end()
                ->scalarNode('timeout')->defaultValue(30)->end()
                ->scalarNode('source_ip')->defaultNull()->end()
                ->scalarNode('encryption')
                    ->defaultNull()
                    ->validate()
                        ->ifNotInArray(array('tls', 'ssl', null))
                        ->thenInvalid('The %s encryption is not supported')
                    ->end()
                ->end()
                ->scalarNode('auth_mode')
                    ->defaultNull()
                    ->validate()
                        ->ifNotInArray(array('plain', 'login', 'cram-md5', null))
                        ->thenInvalid('The %s authentication mode is not supported')
                    ->end()
                ->end()
                ->scalarNode('sender_address')->end()
                ->scalarNode('delivery_address')->end()
                ->arrayNode('antiflood')
                    ->children()
                        ->scalarNode('threshold')->defaultValue(99)->end()
                        ->scalarNode('sleep')->defaultValue(0)->end()
                    ->end()
                ->end()
                ->booleanNode('logging')->defaultValue($this->debug)->end()
                ->arrayNode('spool')
                    ->children()
                        ->scalarNode('type')->defaultValue('file')->end()
                        ->scalarNode('path')->defaultValue('%kernel.cache_dir%/swiftmailer/spool')->end()
                        ->scalarNode('id')->defaultNull()->info('Used by "service" type')->end()
                    ->end()
                    ->validate()
                        ->ifTrue(function ($v) { return 'service' === $v['type'] && empty($v['id']); })
                        ->thenInvalid('You have to configure the service id')
                    ->end()
                ->end()
            ->end()
            ->fixXmlConfig('delivery_whitelist_pattern', 'delivery_whitelist')
            ->children()
                ->arrayNode('delivery_whitelist')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->booleanNode('disable_delivery')->end()
            ->end()
        ;

        return $node;
    }
}
