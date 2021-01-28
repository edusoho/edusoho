<?php

namespace Sentry\SentryBundle;

/**
 * Event names that are triggered to allow for further modification of the
 * Raven client during error processing.
 */
class SentrySymfonyEvents
{

    /**
     * The PRE_CAPTURE event is triggered just before the client captures the
     * exception.
     *
     * @Event("Symfony\Component\EventDispatcher\Event")
     *
     * @var string
     */
    const PRE_CAPTURE = 'sentry.pre_capture';

    /**
     * The SET_USER_CONTEXT event is triggered on requests where the user is
     * authenticated and has authorization.
     *
     * @Event("Sentry\SentryBundle\Event\SentryUserContextEvent")
     *
     * @var string
     */
    const SET_USER_CONTEXT = 'sentry.set_user_context';
}
