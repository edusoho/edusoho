<?php

namespace Sentry\SentryBundle\EventListener;

use Sentry\SentryBundle;
use Sentry\SentryBundle\SentrySymfonyClient;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;

/**
 * Class ExceptionListener
 * @package Sentry\SentryBundle\EventListener
 */
class ExceptionListener
{
    /** @var  TokenStorageInterface */
    private $tokenStorage;

    /** @var  AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var \Raven_Client */
    protected $client;

    /** @var  string[] */
    protected $skipCapture;

    /**
     * ExceptionListener constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param \Raven_Client $client
     * @param array $skipCapture
     */
    public function __construct(
        TokenStorageInterface $tokenStorage = null,
        AuthorizationCheckerInterface $authorizationChecker = null,
        \Raven_Client $client = null,
        array $skipCapture
    ) {
        if (!$client) {
            $client = new SentrySymfonyClient();
        }

        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->client = $client;
        $this->skipCapture = $skipCapture;
    }

    /**
     * @param \Raven_Client $client
     */
    public function setClient(\Raven_Client $client)
    {
        $this->client = $client;
    }

    /**
     * Set the username from the security context by listening on core.request
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if (null === $this->tokenStorage || null === $this->authorizationChecker) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (null !== $token && $this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)) {
            $this->setUserValue($token->getUser());
        }
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        foreach ($this->skipCapture as $className) {
            if ($exception instanceof $className) {
                return;
            }
        }

        $this->client->captureException($exception);
    }

    /**
     * @param ConsoleExceptionEvent $event
     */
    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        $command = $event->getCommand();
        $exception = $event->getException();

        $data = array(
            'tags' => array(
                'command' => $command->getName(),
                'status_code' => $event->getExitCode(),
            ),
        );

        $this->client->captureException($exception, $data);
    }

    /**
     * @param UserInterface | object | string $user
     */
    private function setUserValue($user)
    {
        if ($user instanceof UserInterface) {
            $this->client->set_user_data($user->getUsername());
            return;
        }

        if (is_string($user)) {
            $this->client->set_user_data($user);
            return;
        }

        if (is_object($user) && method_exists($user, '__toString')) {
            $this->client->set_user_data($user->__toString());
        }
    }
}
