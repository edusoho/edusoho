<?php

/*
 * 此文件来自 Silex 项目(https://github.com/silexphp/Silex).
 *
 * 版权信息请看 LICENSE.SILEX
 */

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Context\BootableProviderInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler;
use Monolog\ErrorHandler;
use Symfony\Bridge\Monolog\Handler\DebugHandler;

/**
 * Monolog Provider.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class MonologServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $app)
    {
        $app['logger'] = function () use ($app) {
            return $app['monolog'];
        };

        if ($bridge = class_exists('Symfony\Bridge\Monolog\Logger')) {
            $app['monolog.handler.debug'] = function () use ($app) {
                $level = MonologServiceProvider::translateLevel($app['monolog.level']);

                return new DebugHandler($level);
            };
        }

        $app['monolog.logger.class'] = $bridge ? 'Symfony\Bridge\Monolog\Logger' : 'Monolog\Logger';

        $app['monolog'] = function ($app) {
            $log = new $app['monolog.logger.class']($app['monolog.name']);

            $handler = new Handler\GroupHandler($app['monolog.handlers']);
            if (isset($app['monolog.not_found_activation_strategy'])) {
                $handler = new Handler\FingersCrossedHandler($handler, $app['monolog.not_found_activation_strategy']);
            }

            $log->pushHandler($handler);

            if ($app['debug'] && isset($app['monolog.handler.debug'])) {
                $log->pushHandler($app['monolog.handler.debug']);
            }

            return $log;
        };

        $app['monolog.formatter'] = function () {
            return new LineFormatter();
        };

        $app['monolog.handler'] = $defaultHandler = function () use ($app) {
            $level = MonologServiceProvider::translateLevel($app['monolog.level']);

            $handler = new Handler\StreamHandler($app['monolog.logfile'], $level, $app['monolog.bubble'], $app['monolog.permission']);
            $handler->setFormatter($app['monolog.formatter']);

            return $handler;
        };

        $app['monolog.handlers'] = function () use ($app, $defaultHandler) {
            $handlers = array();

            // enables the default handler if a logfile was set or the monolog.handler service was redefined
            if ($app['monolog.logfile'] || $defaultHandler !== $app->raw('monolog.handler')) {
                $handlers[] = $app['monolog.handler'];
            }

            return $handlers;
        };

        $app['monolog.level'] = function () {
            return Logger::DEBUG;
        };

        $app['monolog.name'] = 'app';
        $app['monolog.bubble'] = true;
        $app['monolog.permission'] = null;
        $app['monolog.exception.logger_filter'] = null;
        $app['monolog.logfile'] = null;
        $app['monolog.use_error_handler'] = function ($app) {
            return !$app['debug'];
        };
    }

    public function boot(Biz $app)
    {
        if ($app['monolog.use_error_handler']) {
            ErrorHandler::register($app['monolog']);
        }
    }

    public static function translateLevel($name)
    {
        // level is already translated to logger constant, return as-is
        if (is_int($name)) {
            return $name;
        }

        $levels = Logger::getLevels();
        $upper = strtoupper($name);

        if (!isset($levels[$upper])) {
            throw new \InvalidArgumentException("Provided logging level '$name' does not exist. Must be a valid monolog logging level.");
        }

        return $levels[$upper];
    }
}
