<?php

namespace Codeages\Weblib\Auth;

use Codeages\Weblib\Error\ErrorCode;

class SignatureTokenAlgo implements TokenAlgo
{
    public function parse($tokenValue)
    {
        $tokenValue = explode(':', $tokenValue);
        if (count($tokenValue) !== 4 || empty($tokenValue[0]) || empty($tokenValue[1]) || empty($tokenValue[2]) || empty($tokenValue[3])) {
            throw new AuthException('Auth token format is invalid.', ErrorCode::INVALID_CREDENTIAL);
        }

        return new Token($tokenValue[0], '', $tokenValue[1], $tokenValue[2], $tokenValue[3]);
    }

    public function check(Token $token, AccessKey $key, $signingText = '')
    {
        if ($token->isReplay()) {
            throw new AuthException("Auth token is replay.", ErrorCode::INVALID_CREDENTIAL);
        }

        if ($token->isExpired()) {
            throw new AuthException("Auth token is expired.", ErrorCode::EXPIRED_CREDENTIAL);
        }

        $signingText = "{$token->once}\n{$token->deadline}\n{$signingText}";

        $signature = $this->signature($signingText, $key->secret);

        if (empty($token->signature) || $token->signature != $signature) {
            throw new AuthException("Signature is invalid.", ErrorCode::INVALID_CREDENTIAL);
        }

        return true;
    }

    public function signature($signingText, $secretKey)
    {
        $signature = hash_hmac('sha1', $signingText, $secretKey, true);
        return  str_replace(array('+', '/'), array('-', '_'), base64_encode($signature));
    }
}