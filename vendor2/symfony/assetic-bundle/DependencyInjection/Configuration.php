<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\AsseticBundle\DependencyInjection;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Christophe Coevoet <stof@notk.org>
 * @author Kris Wallsmith <kris@symfony.com>
 */
class Configuration implements ConfigurationInterface
{
    private $bundles;

    /**
     * Constructor
     *
     * @param array $bundles An array of bundle names
     */
    public function __construct(array $bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $finder = new ExecutableFinder();
        $rootNode = $builder->root('assetic');

        $rootNode
            ->children()
                ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
                ->arrayNode('use_controller')
                    ->addDefaultsIfNotSet()
                    ->treatTrueLike(array('enabled' => true))
                    ->treatFalseLike(array('enabled' => false))
                    ->children()
                        ->booleanNode('enabled')->defaultValue('%kernel.debug%')->end()
                        ->booleanNode('profiler')->defaultFalse()->end()
                    ->end()
                ->end()
                ->scalarNode('read_from')->defaultValue('%kernel.root_dir%/../web')->end()
                ->scalarNode('write_to')->defaultValue('%assetic.read_from%')->end()
                ->scalarNode('java')->defaultValue(function () use ($finder) { return $finder->find('java', '/usr/bin/java'); })->end()
                ->scalarNode('node')->defaultValue(function () use ($finder) { return $finder->find('node', '/usr/bin/node'); })->end()
                ->arrayNode('node_paths')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('ruby')->defaultValue(function () use ($finder) { return $finder->find('ruby', '/usr/bin/ruby'); })->end()
                ->scalarNode('sass')->defaultValue(function () use ($finder) { return $finder->find('sass', '/usr/bin/sass'); })->end()
            ->end()
        ;

        $this->addVariablesSection($rootNode);
        $this->addBundlesSection($rootNode);
        $this->addAssetsSection($rootNode);
        $this->addFiltersSection($rootNode, $finder);
        $this->addWorkersSection($rootNode);
        $this->addTwigSection($rootNode);

        return $builder;
    }

    private function addVariablesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('variable')
            ->children()
                ->arrayNode('variables')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addBundlesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('bundle')
            ->children()
                ->arrayNode('bundles')
                    ->defaultValue($this->bundles)
                    ->prototype('scalar')
                        ->validate()
                            ->ifNotInArray($this->bundles)
                            ->thenInvalid('%s is not a valid bundle.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addAssetsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('asset')
            ->children()
                ->arrayNode('assets')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->beforeNormalization()
                            // a scalar is a simple formula of one input file
                            ->ifTrue(function ($v) { return !is_array($v); })
                            ->then(function ($v) { return array('inputs' => array($v)); })
                        ->end()
                        ->beforeNormalization()
                            ->always()
                            ->then(function ($v) {
                                // cast scalars as array
                                foreach (array('input', 'inputs', 'filter', 'filters') as $key) {
                                    if (isset($v[$key]) && !is_array($v[$key])) {
                                        $v[$key] = array($v[$key]);
                                    }
                                }

                                // organize arbitrary options
                                foreach ($v as $key => $value) {
                                    if (!in_array($key, array('input', 'inputs', 'filter', 'filters', 'option', 'options'))) {
                                        $v['options'][$key] = $value;
                                        unset($v[$key]);
                                    }
                                }

                                return $v;
                            })
                        ->end()

                        // the formula
                        ->fixXmlConfig('input')
                        ->fixXmlConfig('filter')
                        ->children()
                            ->arrayNode('inputs')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('filters')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('options')
                                ->useAttributeAsKey('name')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addFiltersSection(ArrayNodeDefinition $rootNode, ExecutableFinder $finder)
    {
        $rootNode
            ->fixXmlConfig('filter')
            ->children()
                ->arrayNode('filters')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('variable')
                        ->treatNullLike(array())
                        ->validate()
                            ->ifTrue(function ($v) { return !is_array($v); })
                            ->thenInvalid('The assetic.filters config %s must be either null or an array.')
                        ->end()
                    ->end()
                    ->validate()
                        ->always(function ($v) use ($finder) {
                            if (isset($v['compass']) && !isset($v['compass']['bin'])) {
                                $v['compass']['bin'] = $finder->find('compass', '/usr/bin/compass');
                            }

                            return $v;
                        })
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addWorkersSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('workers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('cache_busting')
                            ->treatTrueLike(array('enabled' => true))
                            ->treatFalseLike(array('enabled' => false))
                            ->treatNullLike(array('enabled' => true))
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addTwigSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('twig')
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('function')
                    ->children()
                        ->arrayNode('functions')
                            ->defaultValue(array())
                            ->useAttributeAsKey('name')
                            ->prototype('variable')
                                ->treatNullLike(array())
                                ->validate()
                                    ->ifTrue(function ($v) { return !is_array($v); })
                                    ->thenInvalid('The assetic.twig.functions config %s must be either null or an array.')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
