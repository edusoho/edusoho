<?php

namespace Codeages\Weblib\Auth;

use Codeages\Weblib\Error\ErrorCode;

class SecretTokenAlgo implements TokenAlgo
{
    public function parse($tokenValue)
    {
        $tokenValue = explode(':', $tokenValue);
        if (count($tokenValue) !== 2) {
            throw new AuthException('Auth token format is invalid.', ErrorCode::INVALID_CREDENTIAL);
        }

        return new Token($tokenValue[0], $tokenValue[1]);
    }

    public function check(Token $token, AccessKey $key, $signingText = '')
    {
        if ($token->keySecret != $key->secret) {
            throw new AuthException("Secret key is invalid.", ErrorCode::INVALID_CREDENTIAL);
        }

        return true;
    }
}