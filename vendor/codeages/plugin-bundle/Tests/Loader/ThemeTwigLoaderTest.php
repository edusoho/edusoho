<?php

namespace Codeages\PluginBundle\Tests\Loader;

use Codeages\PluginBundle\Loader\ThemeTwigLoader;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ThemeTwigLoaderTest extends WebTestCase
{
    public function testRenderThemeDirTwigFile()
    {
        $loader = $this->getMockBuilder('Codeages\PluginBundle\Loader\ThemeTwigLoader')
            ->setMethods(array('getCustomFile'))
            ->setConstructorArgs(array($this->mockKernel()))
            ->getMockForAbstractClass();

        $loader->method('getCustomFile')->willReturn(null);  // let custom file not found

        $code = $loader->getSourceContext('default/test.html.twig')->getCode();
        self::assertEquals('THIS A THEME FILE', $code);
    }

    public function testRenderCustomDirTwigFile()
    {
        $loader = new ThemeTwigLoader($this->mockKernel());
        $code = $loader->getSourceContext('default/test.html.twig')->getCode();
        self::assertEquals('THIS A CUSTOM FILE', $code);
    }

    private function mockKernel()
    {
        $testKernel = $this->getMockBuilder('Codeages\PluginBundle\Tests\Loader\Fixture\TestKernel')
            ->setConstructorArgs(array('test', true))
            ->setMethods(array('getPluginConfigurationManager', 'getRootDir'))
            ->getMockForAbstractClass();

        $testKernel->method('getRootDir')->willReturn(__DIR__.DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR.'app');

        $pluginConfigurationManager = $this->getMockBuilder('Codeages\PluginBundle\System\PluginConfigurationManager')
            ->setConstructorArgs(array(__DIR__.DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR.'app'))
            ->setMethods(array('getActiveThemeDirectory', 'getActiveThemeName'))
            ->getMock();

        $pluginConfigurationManager
            ->method('getActiveThemeName')
            ->willReturn('example');

        $pluginConfigurationManager
            ->method('getActiveThemeDirectory')
            ->willReturn(
                __DIR__.DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'example'
            );

        $testKernel
            ->method('getPluginConfigurationManager')
            ->willReturn($pluginConfigurationManager);

        return $testKernel;
    }
}
