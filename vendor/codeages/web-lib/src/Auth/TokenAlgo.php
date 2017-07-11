<?php
namespace Codeages\Weblib\Auth;

interface TokenAlgo
{
    /**
     * @param $tokenValue
     * @return Token
     */
    public function parse($tokenValue);

    /**
     * @param Token $token
     * @param AccessKey $key
     * @param string $signingText
     * @return boolean
     */
    public function check(Token $token, AccessKey $key, $signingText = '');
}
