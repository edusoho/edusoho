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

class EmptyBundlesMultilineShortArraySyntaxKernelStub extends KernelForTest
{
    public function registerBundles()
    {
        $bundles = [

        ];

        return $bundles;
    }
}
