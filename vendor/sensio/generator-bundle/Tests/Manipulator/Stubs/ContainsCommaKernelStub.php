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

class ContainsCommaKernelStub extends KernelForTest
{
    public function registerBundles()
    {
        $bundles = array(
            new StubBundle(),
        );

        return $bundles;
    }
}
