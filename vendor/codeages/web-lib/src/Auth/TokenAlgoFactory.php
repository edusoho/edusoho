<?php
namespace Codeages\Weblib\Auth;

class TokenAlgoFactory
{
    /**
     *
     * @param $strategy
     *
     * @return TokenAlgo
     */
    public function factory($strategy)
    {
        $class = __NAMESPACE__ . "\\" . ucfirst($strategy).'TokenAlgo';

        return new $class();
    }
}