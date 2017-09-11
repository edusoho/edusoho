<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\GeneratorBundle\Tests\Generator;

use Sensio\Bundle\GeneratorBundle\Manipulator\ConfigurationManipulator;
use Sensio\Bundle\GeneratorBundle\Model\Bundle;
use Symfony\Component\Filesystem\Filesystem;

class ConfigurationManipulatorTest extends \PHPUnit_Framework_TestCase
{
    protected $filesystem;
    protected $tmpDir;

    public function setUp()
    {
        $this->tmpDir = sys_get_temp_dir().'/sf';
        $this->filesystem = new Filesystem();
        $this->filesystem->remove($this->tmpDir);
        $this->filesystem->mkdir($this->tmpDir);
    }

    public function tearDown()
    {
        $this->filesystem->remove($this->tmpDir);
    }

    /**
     * @dataProvider getAddResourcesTests
     */
    public function testAddResource($bundleName, $format, $startingContents, $expectedContents)
    {
        $bundle = new Bundle('Acme', $bundleName, 'src', $format, true);

        $configurationPath = $this->tmpDir.'/config.yml';
        file_put_contents($configurationPath, $startingContents);
        $manipulator = new ConfigurationManipulator($configurationPath);

        $manipulator->addResource($bundle);
        $realContents = file_get_contents($configurationPath);
        $this->assertEquals($expectedContents, $realContents);
    }

    public function getAddResourcesTests()
    {
        $tests = array();

        // normal, .yml file
        $tests[] = array(
            'AppBundle',
            'yml',
            <<<EOF
imports:
    - { resource: security.yml }
    - { resource: parameters.yml }
    - { resource: services.yml }
framework:
    esi:             { enabled: true }
    translator:      { fallback: en }
EOF
            , <<<EOF
imports:
    - { resource: security.yml }
    - { resource: parameters.yml }
    - { resource: services.yml }
    - { resource: "@AppBundle/Resources/config/services.yml" }
framework:
    esi:             { enabled: true }
    translator:      { fallback: en }
EOF
        );

        // normal, xml file
        $tests[] = array(
            'AppBundle',
            'xml',
            <<<EOF
imports:
    - { resource: security.yml }
    - { resource: parameters.yml }
    - { resource: services.yml }
framework:
    esi:             { enabled: true }
    translator:      { fallback: en }
EOF
            , <<<EOF
imports:
    - { resource: security.yml }
    - { resource: parameters.yml }
    - { resource: services.yml }
    - { resource: "@AppBundle/Resources/config/services.xml" }
framework:
    esi:             { enabled: true }
    translator:      { fallback: en }
EOF
        );

        // imports further down
        $tests[] = array(
            'AppBundle',
            'yml',
            <<<EOF
framework:
    esi:             { enabled: true }
    translator:      { fallback: en }

imports:
    - { resource: security.yml }
    - { resource: parameters.yml }
    - { resource: services.yml }

twig:
    debug:            "%kernel.debug%"
    strict_variables: "%kernel.debug%"
EOF
            , <<<EOF
framework:
    esi:             { enabled: true }
    translator:      { fallback: en }

imports:
    - { resource: security.yml }
    - { resource: parameters.yml }
    - { resource: services.yml }
    - { resource: "@AppBundle/Resources/config/services.yml" }

twig:
    debug:            "%kernel.debug%"
    strict_variables: "%kernel.debug%"
EOF
        );

        // extra line breaks in the imports list
        $tests[] = array(
            'AppBundle',
            'yml',
            <<<EOF
imports:
    - { resource: security.yml }
    - { resource: parameters.yml }

    - { resource: services.yml }


framework:
    esi:             { enabled: true }
    translator:      { fallback: en }
EOF
            , <<<EOF
imports:
    - { resource: security.yml }
    - { resource: parameters.yml }

    - { resource: services.yml }
    - { resource: "@AppBundle/Resources/config/services.yml" }


framework:
    esi:             { enabled: true }
    translator:      { fallback: en }
EOF
        );

        return $tests;
    }
}
