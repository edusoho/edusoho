<?php

namespace ApiBundle\Security\Authentication;

final class AuthenticationEvents
{
    const BEFORE_AUTHENTICATE = 'api.before_authenticate';

    const AFTER_AUTHENTICATE = 'api.after_authenticate';
}
