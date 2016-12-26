<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SwiftmailerBundle\Tests\DependencyInjection;

use Symfony\Bundle\SwiftmailerBundle\Tests\TestCase;
use Symfony\Bundle\SwiftmailerBundle\DependencyInjection\SwiftmailerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Config\FileLocator;

class SwiftmailerExtensionTest extends TestCase
{
    public function getConfigTypes()
    {
        return array(
            array('xml'),
            array('php'),
            array('yml')
        );
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testDefaultConfig($type)
    {
        $container = $this->loadContainerFromFile('empty', $type);

        $this->assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        $this->assertEquals('swiftmailer.mailer.default.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testSendmailConfig($type)
    {
        $container = $this->loadContainerFromFile('sendmail', $type);

        $this->assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        $this->assertEquals('swiftmailer.mailer.default.transport.sendmail', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testMailConfig($type)
    {
        $container = $this->loadContainerFromFile('mail', $type);

        $this->assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        $this->assertEquals('swiftmailer.mailer.default.transport.mail', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testNullTransport($type)
    {
        $container = $this->loadContainerFromFile('null', $type);

        $this->assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        $this->assertEquals('swiftmailer.mailer.default.transport.null', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testFull($type)
    {
        $container = $this->loadContainerFromFile('full', $type);

        $this->assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        $this->assertEquals('swiftmailer.mailer.default.transport.spool', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
        $this->assertEquals('swiftmailer.mailer.default.transport.real', (string) $container->getAlias('swiftmailer.transport.real'));
        $this->assertEquals('swiftmailer.mailer.default.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.default.transport.real'));
        $this->assertTrue($container->has('swiftmailer.mailer.default.spool.memory'));
        $this->assertEquals('example.org', $container->getParameter('swiftmailer.mailer.default.transport.smtp.host'));
        $this->assertEquals('12345', $container->getParameter('swiftmailer.mailer.default.transport.smtp.port'));
        $this->assertEquals('tls', $container->getParameter('swiftmailer.mailer.default.transport.smtp.encryption'));
        $this->assertEquals('user', $container->getParameter('swiftmailer.mailer.default.transport.smtp.username'));
        $this->assertEquals('pass', $container->getParameter('swiftmailer.mailer.default.transport.smtp.password'));
        $this->assertEquals('login', $container->getParameter('swiftmailer.mailer.default.transport.smtp.auth_mode'));
        $this->assertEquals('1000', $container->getParameter('swiftmailer.mailer.default.transport.smtp.timeout'));
        $this->assertEquals('127.0.0.1', $container->getParameter('swiftmailer.mailer.default.transport.smtp.source_ip'));
        $this->assertSame(array('swiftmailer.default.plugin' => array(array())), $container->getDefinition('swiftmailer.mailer.default.plugin.redirecting')->getTags());
        $this->assertSame('single@host.com', $container->getParameter('swiftmailer.mailer.default.single_address'));
        $this->assertEquals(array('/foo@.*/', '/.*@bar.com$/'), $container->getParameter('swiftmailer.mailer.default.delivery_whitelist'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testManyMailers($type)
    {
        $container = $this->loadContainerFromFile('many_mailers', $type);

        $this->assertEquals('swiftmailer.mailer.secondary_mailer', (string) $container->getAlias('swiftmailer.mailer'));
        $this->assertEquals('swiftmailer.mailer.secondary_mailer.transport', (string) $container->getAlias('swiftmailer.transport'));
        $this->assertEquals('swiftmailer.mailer.secondary_mailer.transport.spool', (string) $container->getAlias('swiftmailer.mailer.secondary_mailer.transport'));
        $this->assertEquals('swiftmailer.mailer.secondary_mailer.transport.spool', (string) $container->getAlias('swiftmailer.mailer.secondary_mailer.transport'));
        $this->assertEquals('example.org', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.host'));
        $this->assertEquals('12345', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.port'));
        $this->assertEquals('tls', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.encryption'));
        $this->assertEquals('user_first', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.username'));
        $this->assertEquals('pass_first', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.password'));
        $this->assertEquals('login', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.auth_mode'));
        $this->assertEquals('1000', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.timeout'));
        $this->assertEquals('127.0.0.1', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.source_ip'));
        $this->assertEquals('example.org', $container->getParameter('swiftmailer.mailer.secondary_mailer.transport.smtp.host'));
        $this->assertEquals('54321', $container->getParameter('swiftmailer.mailer.secondary_mailer.transport.smtp.port'));
        $this->assertEquals('tls', $container->getParameter('swiftmailer.mailer.secondary_mailer.transport.smtp.encryption'));
        $this->assertEquals('user_secondary', $container->getParameter('swiftmailer.mailer.secondary_mailer.transport.smtp.username'));
        $this->assertEquals('pass_secondary', $container->getParameter('swiftmailer.mailer.secondary_mailer.transport.smtp.password'));
        $this->assertEquals('login', $container->getParameter('swiftmailer.mailer.secondary_mailer.transport.smtp.auth_mode'));
        $this->assertEquals('1000', $container->getParameter('swiftmailer.mailer.secondary_mailer.transport.smtp.timeout'));
        $this->assertEquals('127.0.0.1', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.source_ip'));
        $this->assertEquals('example.org', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.host'));
        $this->assertEquals('12345', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.port'));
        $this->assertEquals('tls', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.encryption'));
        $this->assertEquals('user_third', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.username'));
        $this->assertEquals('pass_third', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.password'));
        $this->assertEquals('login', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.auth_mode'));
        $this->assertEquals('1000', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.timeout'));
        $this->assertEquals('127.0.0.1', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.source_ip'));
    }
    /**
     * @dataProvider getConfigTypes
     */
    public function testUrls($type)
    {
        $container = $this->loadContainerFromFile('urls', $type);


        $this->assertEquals('example.com', $container->getParameter('swiftmailer.mailer.smtp_mailer.transport.smtp.host'));
        $this->assertEquals('12345', $container->getParameter('swiftmailer.mailer.smtp_mailer.transport.smtp.port'));
        $this->assertEquals('tls', $container->getParameter('swiftmailer.mailer.smtp_mailer.transport.smtp.encryption'));
        $this->assertEquals('username', $container->getParameter('swiftmailer.mailer.smtp_mailer.transport.smtp.username'));
        $this->assertEquals('password', $container->getParameter('swiftmailer.mailer.smtp_mailer.transport.smtp.password'));
        $this->assertEquals('login', $container->getParameter('swiftmailer.mailer.smtp_mailer.transport.smtp.auth_mode'));
    }

        /**
     * @dataProvider getConfigTypes
     */
    public function testOneMailer($type)
    {
        $container = $this->loadContainerFromFile('one_mailer', $type);

        $this->assertEquals('swiftmailer.mailer.main_mailer.transport', (string) $container->getAlias('swiftmailer.transport'));
        $this->assertEquals('swiftmailer.mailer.main_mailer.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.main_mailer.transport'));
        $this->assertEquals('swiftmailer.mailer.main_mailer.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.main_mailer.transport'));
        $this->assertEquals('example.org', $container->getParameter('swiftmailer.mailer.main_mailer.transport.smtp.host'));
        $this->assertEquals('12345', $container->getParameter('swiftmailer.mailer.main_mailer.transport.smtp.port'));
        $this->assertEquals('tls', $container->getParameter('swiftmailer.mailer.main_mailer.transport.smtp.encryption'));
        $this->assertEquals('user', $container->getParameter('swiftmailer.mailer.main_mailer.transport.smtp.username'));
        $this->assertEquals('pass', $container->getParameter('swiftmailer.mailer.main_mailer.transport.smtp.password'));
        $this->assertEquals('login', $container->getParameter('swiftmailer.mailer.main_mailer.transport.smtp.auth_mode'));
        $this->assertEquals('1000', $container->getParameter('swiftmailer.mailer.main_mailer.transport.smtp.timeout'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testSpool($type)
    {
        $container = $this->loadContainerFromFile('spool', $type);

        $this->assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        $this->assertEquals('swiftmailer.mailer.default.transport.spool', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
        $this->assertEquals('swiftmailer.mailer.default.transport.real', (string) $container->getAlias('swiftmailer.transport.real'));
        $this->assertEquals('swiftmailer.mailer.default.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.default.transport.real'));
        $this->assertTrue($container->has('swiftmailer.mailer.default.spool.file'), 'Default is file based spool');
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testMemorySpool($type)
    {
        $container = $this->loadContainerFromFile('spool_memory', $type);

        $this->assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        $this->assertEquals('swiftmailer.mailer.default.transport.spool', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
        $this->assertEquals('swiftmailer.mailer.default.transport.real', (string) $container->getAlias('swiftmailer.transport.real'));
        $this->assertEquals('swiftmailer.mailer.default.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.default.transport.real'));
        $this->assertTrue($container->has('swiftmailer.mailer.default.spool.memory'), 'Memory based spool is configured');
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testServiceSpool($type)
    {
        $container = $this->loadContainerFromFile('spool_service', $type);

        $this->assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        $this->assertEquals('swiftmailer.mailer.default.transport.spool', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
        $this->assertEquals('swiftmailer.mailer.default.transport.real', (string) $container->getAlias('swiftmailer.transport.real'));
        $this->assertEquals('swiftmailer.mailer.default.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.default.transport.real'));
        $this->assertEquals('custom_service_id', (string) $container->getAlias('swiftmailer.mailer.default.spool.service'));
        $this->assertTrue($container->has('swiftmailer.mailer.default.spool.service'), 'Service based spool is configured');
    }

    /**
     * @dataProvider getConfigTypes
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testInvalidServiceSpool($type)
    {
        $this->loadContainerFromFile('spool_service_invalid', $type);
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testSmtpConfig($type)
    {
        $container = $this->loadContainerFromFile('smtp', $type);

        $this->assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        $this->assertEquals('swiftmailer.mailer.default.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.default.transport'));

        $this->assertEquals('example.org', $container->getParameter('swiftmailer.mailer.default.transport.smtp.host'));
        $this->assertEquals('12345', $container->getParameter('swiftmailer.mailer.default.transport.smtp.port'));
        $this->assertEquals('tls', $container->getParameter('swiftmailer.mailer.default.transport.smtp.encryption'));
        $this->assertEquals('user', $container->getParameter('swiftmailer.mailer.default.transport.smtp.username'));
        $this->assertEquals('pass', $container->getParameter('swiftmailer.mailer.default.transport.smtp.password'));
        $this->assertEquals('login', $container->getParameter('swiftmailer.mailer.default.transport.smtp.auth_mode'));
        $this->assertEquals('1000', $container->getParameter('swiftmailer.mailer.default.transport.smtp.timeout'));
        $this->assertEquals('127.0.0.1', $container->getParameter('swiftmailer.mailer.default.transport.smtp.source_ip'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testRedirectionConfig($type)
    {
        $container = $this->loadContainerFromFile('redirect', $type);

        $this->assertSame(array('swiftmailer.default.plugin' => array(array())), $container->getDefinition('swiftmailer.mailer.default.plugin.redirecting')->getTags());
        $this->assertSame('single@host.com', $container->getParameter('swiftmailer.mailer.default.single_address'));
        $this->assertEquals(array('/foo@.*/', '/.*@bar.com$/'), $container->getParameter('swiftmailer.mailer.default.delivery_whitelist'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testSingleRedirectionConfig($type)
    {
        $container = $this->loadContainerFromFile('redirect_single', $type);

        $this->assertSame(array('swiftmailer.default.plugin' => array(array())), $container->getDefinition('swiftmailer.mailer.default.plugin.redirecting')->getTags());
        $this->assertSame('single@host.com', $container->getParameter('swiftmailer.mailer.default.single_address'));
        $this->assertSame(array('single@host.com'), $container->getParameter('swiftmailer.mailer.default.delivery_addresses'));
        $this->assertEquals(array('/foo@.*/'), $container->getParameter('swiftmailer.mailer.default.delivery_whitelist'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testMultiRedirectionConfig($type)
    {
        $container = $this->loadContainerFromFile('redirect_multi', $type);

        $this->assertSame(array('swiftmailer.default.plugin' => array(array())), $container->getDefinition('swiftmailer.mailer.default.plugin.redirecting')->getTags());
        $this->assertSame(array('first@host.com', 'second@host.com'), $container->getParameter('swiftmailer.mailer.default.delivery_addresses'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testAntifloodConfig($type)
    {
        $container = $this->loadContainerFromFile('antiflood', $type);

        $this->assertSame(array('swiftmailer.default.plugin' => array(array())), $container->getDefinition('swiftmailer.mailer.default.plugin.antiflood')->getTags());
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testSenderAddress($type)
    {
        $container = $this->loadContainerFromFile('sender_address', $type);

        $this->assertEquals('noreply@test.com', $container->getParameter('swiftmailer.mailer.default.sender_address'));
        $this->assertEquals('noreply@test.com', $container->getParameter('swiftmailer.sender_address'));
        $this->assertTrue($container->hasParameter('swiftmailer.mailer.default.sender_address'), 'The sender address is configured');
    }

    /**
     * @param  string           $file
     * @param  string           $type
     * @return ContainerBuilder
     */
    private function loadContainerFromFile($file, $type)
    {
        $container = new ContainerBuilder();

        $container->setParameter('kernel.debug', false);
        $container->setParameter('kernel.cache_dir', '/tmp');

        $container->registerExtension(new SwiftmailerExtension());
        $locator = new FileLocator(__DIR__ . '/Fixtures/config/' . $type);

        switch ($type) {
            case 'xml':
                $loader = new XmlFileLoader($container, $locator);
                break;

            case 'yml':
                $loader = new YamlFileLoader($container, $locator);
                break;

            case 'php':
                $loader = new PhpFileLoader($container, $locator);
                break;
        }

        $loader->load($file . '.' . $type);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
