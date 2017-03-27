<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\GeneratorBundle\Command\AutoComplete;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Provides auto-completion suggestions for entities.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class EntitiesAutoCompleter
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function getSuggestions()
    {
        $configuration = $this->manager
            ->getConfiguration()
        ;

        $namespaceReplacements = array();

        foreach ($configuration->getEntityNamespaces() as $alias => $namespace) {
            $namespaceReplacements[$namespace.'\\'] = $alias.':';
        }

        $entities = $configuration
            ->getMetadataDriverImpl()
            ->getAllClassNames()
        ;

        return array_map(function ($entity) use ($namespaceReplacements) {
            return strtr($entity, $namespaceReplacements);
        }, $entities);
    }
}
