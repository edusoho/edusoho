<?php

namespace Codeages\Weblib\Auth;

use Codeages\Weblib\Error\ErrorCode;

class Authentication
{
    /**
     * @var KeyProvider
     */
    protected $keyProvider;

    /**
     * @var TokenAlgoFactory
     */
    protected $tokenFactory;

    protected $tokenHeaderKey = 'Authorization';

    public function __construct(KeyProvider $keyProvider, TokenAlgoFactory $tokenFactory = null, array $options = array())
    {
        $this->keyProvider = $keyProvider;

        if (empty($tokenFactory)) {
            $tokenFactory = new TokenAlgoFactory();
        }
        $this->tokenFactory = $tokenFactory;

        if (isset($options['token_header_key'])) {
            $this->tokenHeaderKey = $options['token_header_key'];
        }
    }

    public function auth($request)
    {
        $header = $this->getTokenHeader($request);
        $header = explode(' ', $header);
        if (count($header) !== 2) {
            throw new AuthException("Authorization header is invalid.", ErrorCode::INVALID_CREDENTIAL);
        }

        list($strategy, $token) = $header;

        $strategy = $this->tokenFactory->factory($strategy);

        $token = $strategy->parse($token);

        $key = $this->keyProvider->get($token->keyId);

        if (empty($key)) {
            throw new AuthException("Key id is not exist.", ErrorCode::INVALID_CREDENTIAL);
        }

        if ($key->getLimitIps()) {
            $ip = $this->getClientIp($request);
            if (!in_array($ip, $key->getLimitIps())) {
                throw new AuthException("Your ip `{$ip}` is not allowed.", ErrorCode::FORBIDDEN);
            }
        }

        $strategy->check($token, $key, $this->getRequestText($request));

        if ($key->isInactive()) {
            throw new AuthException("Key is banned.", ErrorCode::BANNED_CREDENTIALS);
        }

        if ($key->isDeleted()) {
            throw new AuthException("Key is deleted.", ErrorCode::BANNED_CREDENTIALS);
        }

        if ($key->isExpired()) {
            throw new AuthException("Key is expired.", ErrorCode::EXPIRED_CREDENTIAL);
        }

        return $key;
    }

    public function getTokenHeader($request)
    {
        if ($request instanceof \Phalcon\Http\Request) {
            return $request->getHeader('Authorization');
        } elseif ($request instanceof \Symfony\Component\HttpFoundation\Request) {
            return $request->headers->get('Authorization');
        }
        throw new \InvalidArgumentException("Request class is not supported.");
    }

    public function getRequestText($request)
    {
        if ($request instanceof \Phalcon\Http\Request) {
            $uri = $request->getURI();
            $body = $request->getRawBody();
        } elseif ($request instanceof \Symfony\Component\HttpFoundation\Request) {
            $uri = $request->getRequestUri();
            $body = $request->getContent();
        } else {
            throw new \InvalidArgumentException("Request class is not supported.");
        }

        return "{$uri}\n{$body}";
    }

    public function getClientIp($request)
    {
        if ($request instanceof \Phalcon\Http\Request) {
            return $request->getClientAddress(true);
        } elseif ($request instanceof \Symfony\Component\HttpFoundation\Request) {
            return $request->getClientIp();
        }
        throw new \InvalidArgumentException("Request class is not supported.");
    }
}