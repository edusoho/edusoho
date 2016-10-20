<?php
namespace Codeages\RestApiClient\Tests;

use Psr\Log\LoggerInterface;

class TestLogger implements LoggerInterface
{
    public function emergency($message, array $context = array())
    {
        return $this->log('EMERGENCY', $message, $context);
    }

    public function alert($message, array $context = array())
    {
        return $this->log('ALERT', $message, $context);
    }

    public function critical($message, array $context = array())
    {
        return $this->log('CRITICAL', $message, $context);
    }

    public function error($message, array $context = array())
    {
        return $this->log('ERROR', $message, $context);
    }

    public function warning($message, array $context = array())
    {
        return $this->log('WARNING', $message, $context);
    }

    public function notice($message, array $context = array())
    {
        return $this->log('NOTICE', $message, $context);
    }

    public function info($message, array $context = array())
    {
        return $this->log('INFO', $message, $context);
    }

    public function debug($message, array $context = array())
    {
        return $this->log('DEBUG', $message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        echo "\n" . date('Y-m-d H:i:s') . " {$level} $message " . json_encode($context) . "\n";
        return ;
    }

}