<?php
namespace Topxia\Component\Payment;

class Payment
{
    public static function createRequest($name, $options = array())
    {
        $name  = ucfirst(strtolower($name));
        $class = __NAMESPACE__."\\{$name}\\{$name}Request";

        if (!class_exists($class)) {
            throw new \Exception("Payment request {$name} is not exist!");
        }

        return new $class($options);
    }

    public static function createCloseTradeRequest($name, $options = array())
    {
        $name  = ucfirst(strtolower($name));
        $class = __NAMESPACE__."\\{$name}\\{$name}CloseTradeRequest";

        if (!class_exists($class)) {
            throw new \Exception("Payment close trade request {$name} is not exist!");
        }

        return new $class($options);
    }

    public static function createAuthBankRequest($name, $options = array())
    {
        $name  = ucfirst(strtolower($name));
        $class = __NAMESPACE__."\\{$name}\\{$name}AuthBankRequest";

        if (!class_exists($class)) {
            throw new \Exception("Payment Auth Bank request {$name} is not exist!");
        }

        return new $class($options);
    }

    public static function createUnbindAuthRequest($name, $options = array())
    {
        $name  = ucfirst(strtolower($name));
        $class = __NAMESPACE__."\\{$name}\\{$name}UnbindAuthRequest";

        if (!class_exists($class)) {
            throw new \Exception("Payment Unbind Auth request {$name} is not exist!");
        }

        return new $class($options);
    }

    public static function createResponse($name, $options = array())
    {
        $name  = ucfirst(strtolower($name));
        $class = __NAMESPACE__."\\{$name}\\{$name}Response";

        if (!class_exists($class)) {
            throw new \Exception("Payment response {$name} is not exist!");
        }

        return new $class($options);
    }

    public static function createTradeQueryRequest($name, $options = array())
    {
        $name  = ucfirst(strtolower($name));
        $class = __NAMESPACE__."\\{$name}\\{$name}TradeQueryRequest";

        if (!class_exists($class)) {
            throw new \Exception("Payment Trade Query request {$name} is not exist!");
        }

        return new $class($options);
    }

    private function __construct()
    {}
}
