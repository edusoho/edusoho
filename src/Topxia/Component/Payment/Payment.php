<?php
namespace Topxia\Component\Payment;

class Payment {

    public static function createRequest($name, $options = array()) {
        $name = ucfirst(strtolower($name));
        $class = __NAMESPACE__ . "\\{$name}\\{$name}Request";

        if (!class_exists($class)) {
            throw new \Exception("Payment request {$name} is not exist!");
        }
        return new $class($options);
    }

    public static function createResponse($name, $options = array()) {
        $name = ucfirst(strtolower($name));
        $class = __NAMESPACE__ . "\\{$name}\\{$name}Response";

        if (!class_exists($class)) {
            throw new \Exception("Payment response {$name} is not exist!");
        }
        return new $class($options);
    }

    private function __construct() { }
}