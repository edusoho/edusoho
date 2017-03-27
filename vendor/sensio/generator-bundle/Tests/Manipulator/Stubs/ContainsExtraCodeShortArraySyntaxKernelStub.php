<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KernelManipulatorTest\Stubs;

use Symfony\Component\HttpKernel\Tests\Fixtures\KernelForTest;
use Sensio\Bundle\GeneratorBundle\Tests\Manipulator\Stubs\StubBundle;

class ContainsExtraCodeShortArraySyntaxKernelStub extends KernelForTest
{
    public function registerBundles()
    {
        $someVariable = false;

        // some bad formatted definition
        $bundles = [
            new StubBundle(),

            new StubBundle(),

        ];

        if (!$someVariable && in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new StubBundle();
        }

        return $bundles;
    }
}
