<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\GeneratorBundle\Tests\Manipulator;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\PhpExecutableFinder;
use Sensio\Bundle\GeneratorBundle\Tests\Generator\GeneratorTest;
use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;

class KernelManipulatorTest extends GeneratorTest
{
    const STUB_BUNDLE_CLASS_NAME = 'Sensio\\Bundle\\GeneratorBundle\\Tests\\Manipulator\\Stubs\\StubBundle';
    const STUB_NAMESPACE = 'KernelManipulatorTest\\Stubs';

    /**
     * @dataProvider kernelStubFilenamesProvider
     *
     * @param string $kernelOriginFilePath
     */
    public function testAddToArray($kernelOriginFilePath)
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not supported in HHVM since it doesn\'t allow to lint PHP files.');
        }

        $params = $this->prepareTestKernel($kernelOriginFilePath);

        list($kernelClassName, $fullpath) = $params;
        $kernelClassName = self::STUB_NAMESPACE.'\\'.$kernelClassName;
        $this->registerClassLoader($kernelClassName, $fullpath);

        $kernel = new  $kernelClassName('test', true);
        $manipulator = new KernelManipulator($kernel);
        $manipulator->addBundle(self::STUB_BUNDLE_CLASS_NAME);

        $phpFinder = new PhpExecutableFinder();
        $phpExecutable = $phpFinder->find();

        $this->assertNotSame(false, $phpExecutable, 'Php executable binary found');

        $pb = new ProcessBuilder();
        $process = $pb->add($phpExecutable)->add('-l')->add($fullpath)->getProcess();
        $process->run();

        $result = strpos($process->getOutput(), 'No syntax errors detected');
        $this->assertNotSame(false, $result, 'Manipulator should not provoke syntax errors');
    }

    /**
     * @return array
     */
    public function kernelStubFilenamesProvider()
    {
        $stubs = array(
            'With empty bundles array' => array(__DIR__.'/Stubs/EmptyBundlesKernelStub.php'),
            'With empty multiline bundles array' => array(__DIR__.'/Stubs/EmptyBundlesMultilineKernelStub.php'),
            'With bundles array contains comma' => array(__DIR__.'/Stubs/ContainsCommaKernelStub.php'),
            'With bundles added w/o trailing comma' => array(__DIR__.'/Stubs/ContainsBundlesKernelStub.php'),
            'With some extra code and bad formatted' => array(__DIR__.'/Stubs/ContainsExtraCodeKernelStub.php'),

        );

        if(PHP_VERSION_ID >= 50400){
            $stubs = array_merge($stubs, array(
                'With empty bundles array, short array syntax' => array(__DIR__.'/Stubs/EmptyBundlesShortArraySyntaxKernelStub.php'),
                'With empty multiline bundles array, short array syntax' => array(__DIR__.'/Stubs/EmptyBundlesMultilineShortArraySyntaxKernelStub.php'),
                'With bundles array contains comma, short array syntax' => array(__DIR__.'/Stubs/ContainsCommaShortArraySyntaxKernelStub.php'),
                'With bundles added w/o trailing comma, short array syntax' => array(__DIR__.'/Stubs/ContainsBundlesShortArraySyntaxKernelStub.php'),
            ));
        }

        return $stubs;
    }

    /**
     * Copies stub file to tmp.
     *
     * @param string $kernelOriginFilePath
     *
     * @return array
     */
    protected function prepareTestKernel($kernelOriginFilePath)
    {
        $pathInfo = pathinfo($kernelOriginFilePath);
        $fileName = $pathInfo['basename'];
        $className = $pathInfo['filename'];

        $targetDir = $this->tmpDir.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, self::STUB_NAMESPACE);
        $this->filesystem->mkdir($targetDir);

        $targetPath = $targetDir.DIRECTORY_SEPARATOR.$fileName;
        $this->filesystem->copy($kernelOriginFilePath, $targetPath, true);

        return array($className, $targetPath);
    }

    /**
     * Registers the stubs namespace in the autoloader.
     *
     * @param string $kernelClassName
     * @param string $fullpath
     */
    protected function registerClassLoader($kernelClassName, $fullpath)
    {
        spl_autoload_register(
            function ($class) use ($kernelClassName, $fullpath) {
                if ($class === $kernelClassName) {
                    require $fullpath;

                    return true;
                }
            }
        );
    }
}
