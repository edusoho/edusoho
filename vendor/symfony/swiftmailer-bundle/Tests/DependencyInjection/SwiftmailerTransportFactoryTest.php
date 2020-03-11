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

use Symfony\Bundle\SwiftmailerBundle\DependencyInjection\SwiftmailerTransportFactory;
use Symfony\Component\Routing\RequestContext;

class SwiftmailerTransportFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateTransportWithSmtp()
    {
        $options = array(
            'transport' => 'smtp',
            'username' => 'user',
            'password' => 'pass',
            'host' => 'host',
            'port' => 1234,
            'timeout' => 42,
            'source_ip' => 'source_ip',
            'local_domain' => 'local_domain',
            'encryption' => 'encryption',
            'auth_mode' => 'auth_mode',
        );

        $transport = SwiftmailerTransportFactory::createTransport(
            $options,
            new RequestContext(),
            new \Swift_Events_SimpleEventDispatcher()
        );
        $this->assertInstanceOf('Swift_Transport_EsmtpTransport', $transport);
        $this->assertSame($transport->getHost(), $options['host']);
        $this->assertSame($transport->getPort(), $options['port']);
        $this->assertSame($transport->getEncryption(), $options['encryption']);
        $this->assertSame($transport->getTimeout(), $options['timeout']);
        $this->assertSame($transport->getSourceIp(), $options['source_ip']);

        $authHandler = current($transport->getExtensionHandlers());
        $this->assertSame($authHandler->getUsername(), $options['username']);
        $this->assertSame($authHandler->getPassword(), $options['password']);
        $this->assertSame($authHandler->getAuthMode(), $options['auth_mode']);
    }

    public function testCreateTransportWithSendmail()
    {
        $options = array(
            'transport' => 'sendmail',
        );

        $transport = SwiftmailerTransportFactory::createTransport(
            $options,
            new RequestContext(),
            new \Swift_Events_SimpleEventDispatcher()
        );
        $this->assertInstanceOf('Swift_Transport_SendmailTransport', $transport);
    }

    /**
     * @group legacy
     */
    public function testCreateTransportWithMail()
    {
        $options = array(
            'transport' => 'mail',
        );

        $transport = SwiftmailerTransportFactory::createTransport(
            $options,
            new RequestContext(),
            new \Swift_Events_SimpleEventDispatcher()
        );
        $this->assertInstanceOf('Swift_Transport_MailTransport', $transport);
    }

    public function testCreateTransportWithNull()
    {
        $options = array(
            'transport' => 'null',
        );

        $transport = SwiftmailerTransportFactory::createTransport(
            $options,
            new RequestContext(),
            new \Swift_Events_SimpleEventDispatcher()
        );
        $this->assertInstanceOf('Swift_Transport_NullTransport', $transport);
    }

    public function testCreateTransportWithSmtpAndWithoutRequestContext()
    {
        $options = array(
            'transport' => 'smtp',
            'username' => 'user',
            'password' => 'pass',
            'host' => 'host',
            'port' => 1234,
            'timeout' => 42,
            'source_ip' => 'source_ip',
            'local_domain' => 'local_domain',
            'encryption' => 'encryption',
            'auth_mode' => 'auth_mode',
        );

        $transport = SwiftmailerTransportFactory::createTransport(
            $options,
            null,
            new \Swift_Events_SimpleEventDispatcher()
        );
        $this->assertInstanceOf('Swift_Transport_EsmtpTransport', $transport);
        $this->assertSame($transport->getHost(), $options['host']);
        $this->assertSame($transport->getPort(), $options['port']);
        $this->assertSame($transport->getEncryption(), $options['encryption']);
        $this->assertSame($transport->getTimeout(), $options['timeout']);
        $this->assertSame($transport->getSourceIp(), $options['source_ip']);

        $authHandler = current($transport->getExtensionHandlers());
        $this->assertSame($authHandler->getUsername(), $options['username']);
        $this->assertSame($authHandler->getPassword(), $options['password']);
        $this->assertSame($authHandler->getAuthMode(), $options['auth_mode']);
    }

    /**
     * @dataProvider optionsAndResultExpected
     */
    public function testResolveOptions($options, $expected)
    {
        $result = SwiftmailerTransportFactory::resolveOptions($options);
        $this->assertEquals($expected, $result);
    }

    public function optionsAndResultExpected()
    {
        return array(
            array(
                array(
                    'url' => '',
                ),
                array(
                    'transport' => 'null',
                    'username' => null,
                    'password' => null,
                    'host' => null,
                    'port' => 25,
                    'timeout' => null,
                    'source_ip' => null,
                    'local_domain' => null,
                    'encryption' => null,
                    'auth_mode' => null,
                    'url' => '',
                ),
            ),
            array(
                array(
                    'url' => 'smtp://user:pass@host:1234',
                ),
                array(
                    'transport' => 'smtp',
                    'username' => 'user',
                    'password' => 'pass',
                    'host' => 'host',
                    'port' => 1234,
                    'timeout' => null,
                    'source_ip' => null,
                    'local_domain' => null,
                    'encryption' => null,
                    'auth_mode' => null,
                    'url' => 'smtp://user:pass@host:1234',
                ),
            ),
            array(
                array(
                    'url' => 'smtp://user:pass@host:1234?transport=sendmail&username=username&password=password&host=example.com&port=5678',
                ),
                array(
                    'transport' => 'sendmail',
                    'username' => 'username',
                    'password' => 'password',
                    'host' => 'example.com',
                    'port' => 5678,
                    'timeout' => null,
                    'source_ip' => null,
                    'local_domain' => null,
                    'encryption' => null,
                    'auth_mode' => null,
                    'url' => 'smtp://user:pass@host:1234?transport=sendmail&username=username&password=password&host=example.com&port=5678',
                ),
            ),
            array(
                array(
                    'url' => 'smtp://user:pass@host:1234?timeout=42&source_ip=source_ip&local_domain=local_domain&encryption=encryption&auth_mode=auth_mode',
                ),
                array(
                    'transport' => 'smtp',
                    'username' => 'user',
                    'password' => 'pass',
                    'host' => 'host',
                    'port' => 1234,
                    'timeout' => 42,
                    'source_ip' => 'source_ip',
                    'local_domain' => 'local_domain',
                    'encryption' => 'encryption',
                    'auth_mode' => 'auth_mode',
                    'url' => 'smtp://user:pass@host:1234?timeout=42&source_ip=source_ip&local_domain=local_domain&encryption=encryption&auth_mode=auth_mode',
                ),
            ),
            array(
                array(),
                array(
                    'transport' => 'null',
                    'username' => null,
                    'password' => null,
                    'host' => null,
                    'port' => 25,
                    'timeout' => null,
                    'source_ip' => null,
                    'local_domain' => null,
                    'encryption' => null,
                    'auth_mode' => null,
                ),
            ),
            array(
                array(
                    'transport' => 'smtp',
                ),
                array(
                    'transport' => 'smtp',
                    'username' => null,
                    'password' => null,
                    'host' => null,
                    'port' => 25,
                    'timeout' => null,
                    'source_ip' => null,
                    'local_domain' => null,
                    'encryption' => null,
                    'auth_mode' => null,
                ),
            ),
            array(
                array(
                    'transport' => 'gmail',
                ),
                array(
                    'transport' => 'smtp',
                    'username' => null,
                    'password' => null,
                    'host' => 'smtp.gmail.com',
                    'port' => 465,
                    'timeout' => null,
                    'source_ip' => null,
                    'local_domain' => null,
                    'encryption' => 'ssl',
                    'auth_mode' => 'login',
                ),
            ),
            array(
                array(
                    'transport' => 'sendmail',
                ),
                array(
                    'transport' => 'sendmail',
                    'username' => null,
                    'password' => null,
                    'host' => null,
                    'port' => 25,
                    'timeout' => null,
                    'source_ip' => null,
                    'local_domain' => null,
                    'encryption' => null,
                    'auth_mode' => null,
                ),
            ),
            array(
                array(
                    'encryption' => 'ssl',
                ),
                array(
                    'transport' => 'null',
                    'username' => null,
                    'password' => null,
                    'host' => null,
                    'port' => 465,
                    'timeout' => null,
                    'source_ip' => null,
                    'local_domain' => null,
                    'encryption' => 'ssl',
                    'auth_mode' => null,
                ),
            ),
            array(
                array(
                    'port' => 42,
                ),
                array(
                    'transport' => 'null',
                    'username' => null,
                    'password' => null,
                    'host' => null,
                    'port' => 42,
                    'timeout' => null,
                    'source_ip' => null,
                    'local_domain' => null,
                    'encryption' => null,
                    'auth_mode' => null,
                ),
            ),
        );
    }
}
