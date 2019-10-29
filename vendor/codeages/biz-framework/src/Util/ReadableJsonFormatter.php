<?php
namespace Codeages\Biz\Framework\Util;

use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;

/**
 * 同时兼顾了人类可读以及便于日志收集系统解析的日志格式类
 */
class ReadableJsonFormatter extends JsonFormatter
{
    protected $dateFormat = 'Y-m-d\TH:i:s.uP';
 
    /**
     * Translates Monolog log levels to syslog levels.
     * @see https://en.wikipedia.org/wiki/Syslog
     */
    private $logLevels = array(
        Logger::DEBUG     => 7,
        Logger::INFO      => 6,
        Logger::NOTICE    => 5,
        Logger::WARNING   => 4,
        Logger::ERROR     => 3,
        Logger::CRITICAL  => 2,
        Logger::ALERT     => 1,
        Logger::EMERGENCY => 0,
    );
 
    public function format(array $record)
    {
        if (!isset($record['datetime'])) {
            return parent::format($record);
        }
 
        return parent::format(array(
            'time' => $record['datetime']->format($this->dateFormat),
            'channel' => $record['channel'],
            'level_name' => $record['level_name'],
            'message' => $record['message'],
            'context' => $record['context'],
            'extra' => $record['extra'],
            'level' => $this->logLevels[$record['level']],
        ));
    }
}