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

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Encapsulates logic for creating a directory resource.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class DirectoryResourceDefinition extends Definition
{
    /**
     * Constructor.
     *
     * @param string $bundle A bundle name or empty string
     * @param string $engine The templating engine
     * @param array  $dirs   An array of directories to merge
     */
    public function __construct($bundle, $engine, array $dirs)
    {
        if (!count($dirs)) {
            throw new \InvalidArgumentException('You must provide at least one directory.');
        }

        parent::__construct();

        $this
            ->addTag('assetic.templating.'.$engine)
            ->addTag('assetic.formula_resource', array('loader' => $engine));
        ;

        if (1 == count($dirs)) {
            // no need to coalesce
            self::configureDefinition($this, $bundle, $engine, reset($dirs));

            return;
        }

        // gather the wrapped resource definitions
        $resources = array();
        foreach ($dirs as $dir) {
            $resources[] = $resource = new Definition();
            self::configureDefinition($resource, $bundle, $engine, $dir);
        }

        $this
            ->setClass('%assetic.coalescing_directory_resource.class%')
            ->addArgument($resources)
            ->setPublic(false)
        ;
    }

    private static function configureDefinition(Definition $definition, $bundle, $engine, $dir)
    {
        $definition
            ->setClass('%assetic.directory_resource.class%')
            ->addArgument(new Reference('templating.loader'))
            ->addArgument($bundle)
            ->addArgument($dir)
            ->addArgument('/\.[^.]+\.'.$engine.'$/')
            ->setPublic(false)
        ;
    }
}
