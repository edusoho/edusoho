<?php

namespace Fomalhaut\WechatBundle\DependencyInjection;

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
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('wechat');

        // 第一个子节点是一个布尔节点，默认为false
        $rootNode
            ->children()
                ->booleanNode('debug')->defaultFalse()->end()
            ->end();

        // 第二个节点是一个数组节点
        $rootNode
            ->children()
                ->arrayNode('clients')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('token')->isRequired()->end()
                            ->scalarNode('appid')->defaultNull()->end()
                            ->scalarNode('appsecret')->defaultNull()->end()
                        ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
