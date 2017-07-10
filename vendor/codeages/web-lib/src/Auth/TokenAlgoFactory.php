<?php
namespace Codeages\Weblib\Auth;

class TokenAlgoFactory
{
    protected $container;

    /**
     * TokenAlgoFactory constructor.
     *
     * @param \Pimple\Container|null $container
     */
    public function __construct($container = null)
    {
        if (!$container) {
            return ;
        }

        if ($container instanceof \Pimple\Container) {
            $this->container = $container;
            return ;
        }

        throw new \InvalidArgumentException("TokenAlgoFactory only support `\Pimple\Container` container.");
    }

    /**
     *
     * @param $strategy string Token算法名称
     *
     * @return TokenAlgo
     */
    public function factory($strategy)
    {
        if ($this->container) {
            return $this->container['weblib.auth.token_algo.'.strtolower($strategy)];
        } else {
            $class = __NAMESPACE__ . "\\" . ucfirst($strategy).'TokenAlgo';
            return new $class();
        }
    }
}