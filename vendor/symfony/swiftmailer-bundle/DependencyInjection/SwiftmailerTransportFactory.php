<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SwiftmailerBundle\DependencyInjection;

use Symfony\Component\Routing\RequestContext;

/**
 * Factory to create a \Swift_Transport object.
 *
 * @author Romain Gautier <mail@romain.sh>
 */
class SwiftmailerTransportFactory
{
    /**
     * @param array                         $options
     * @param RequestContext|null           $requestContext
     * @param \Swift_Events_EventDispatcher $eventDispatcher
     *
     * @return \Swift_Transport
     *
     * @throws \InvalidArgumentException if the scheme is not a built-in Swiftmailer transport
     */
    public static function createTransport(array $options, RequestContext $requestContext = null, \Swift_Events_EventDispatcher $eventDispatcher)
    {
        $options = static::resolveOptions($options);

        if ('smtp' === $options['transport']) {
            $smtpAuthHandler = new \Swift_Transport_Esmtp_AuthHandler(array(
                new \Swift_Transport_Esmtp_Auth_CramMd5Authenticator(),
                new \Swift_Transport_Esmtp_Auth_LoginAuthenticator(),
                new \Swift_Transport_Esmtp_Auth_PlainAuthenticator(),
            ));
            $smtpAuthHandler->setUsername($options['username']);
            $smtpAuthHandler->setPassword($options['password']);
            $smtpAuthHandler->setAuthMode($options['auth_mode']);

            $transport = new \Swift_Transport_EsmtpTransport(
                new \Swift_Transport_StreamBuffer(new \Swift_StreamFilters_StringReplacementFilterFactory()),
                array($smtpAuthHandler),
                $eventDispatcher
            );
            $transport->setHost($options['host']);
            $transport->setPort($options['port']);
            $transport->setEncryption($options['encryption']);
            $transport->setTimeout($options['timeout']);
            $transport->setSourceIp($options['source_ip']);

            $smtpTransportConfigurator = new SmtpTransportConfigurator(null, $requestContext);
            $smtpTransportConfigurator->configure($transport);
        } elseif ('sendmail' === $options['transport']) {
            $transport = new \Swift_Transport_SendmailTransport(
                new \Swift_Transport_StreamBuffer(new \Swift_StreamFilters_StringReplacementFilterFactory()),
                $eventDispatcher
            );

            $smtpTransportConfigurator = new SmtpTransportConfigurator(null, $requestContext);
            $smtpTransportConfigurator->configure($transport);
        } elseif ('mail' === $options['transport']) {
            $transport = new \Swift_Transport_MailTransport(new \Swift_Transport_SimpleMailInvoker(), $eventDispatcher);
        } elseif ('null' === $options['transport']) {
            $transport = new \Swift_Transport_NullTransport($eventDispatcher);
        } else {
            throw new \InvalidArgumentException(sprintf('Not a built-in Swiftmailer transport: %s.', $options['transport']));
        }

        return $transport;
    }

    /**
     * @param array $options
     *
     * @return array options
     */
    public static function resolveOptions(array $options)
    {
        $options += array(
            'transport' => null,
            'username' => null,
            'password' => null,
            'host' => null,
            'port' => null,
            'timeout' => null,
            'source_ip' => null,
            'local_domain' => null,
            'encryption' => null,
            'auth_mode' => null,
        );

        if (isset($options['url'])) {
            $parts = parse_url($options['url']);
            if (isset($parts['scheme'])) {
                $options['transport'] = $parts['scheme'];
            }
            if (isset($parts['user'])) {
                $options['username'] = $parts['user'];
            }
            if (isset($parts['pass'])) {
                $options['password'] = $parts['pass'];
            }
            if (isset($parts['host'])) {
                $options['host'] = $parts['host'];
            }
            if (isset($parts['port'])) {
                $options['port'] = $parts['port'];
            }
            if (isset($parts['query'])) {
                parse_str($parts['query'], $query);
                foreach ($options as $key => $value) {
                    if (isset($query[$key])) {
                        $options[$key] = $query[$key];
                    }
                }
            }
        }

        if (!isset($options['transport'])) {
            $options['transport'] = 'null';
        } elseif ('gmail' === $options['transport']) {
            $options['encryption'] = 'ssl';
            $options['auth_mode'] = 'login';
            $options['host'] = 'smtp.gmail.com';
            $options['transport'] = 'smtp';
        }

        if (!isset($options['port'])) {
            $options['port'] = 'ssl' === $options['encryption'] ? 465 : 25;
        }

        return $options;
    }
}
