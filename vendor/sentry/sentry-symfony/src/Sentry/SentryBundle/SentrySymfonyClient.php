<?php

namespace Sentry\SentryBundle;

class SentrySymfonyClient extends \Raven_Client
{
    public function __construct($dsn=null, $options=array(), $error_types='')
    {
        if (is_string($error_types) && !empty($error_types)) {
            $exParser = new ErrorTypesParser($error_types);
            $options['error_types'] = $exParser->parse();
        }

        $options['sdk'] = array(
            'name' => 'sentry-symfony',
            'version' => SentryBundle::VERSION,
        );
        $options['tags']['symfony_version'] = \Symfony\Component\HttpKernel\Kernel::VERSION;

        parent::__construct($dsn, $options);
    }
}
