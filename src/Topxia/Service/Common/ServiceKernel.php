<?php
namespace Topxia\Service\Common;

class ServiceKernel
{

    private static $_instance;

    protected $environment;
    protected $debug;
    protected $booted;
    protected $container;
    protected $rootPath;

    protected $pool = array();

    public static function create($container, $environment, $debug)
    {
        if (self::$_instance) {
            return self::$_instance;
        }

        $instance = new self();
        $instance->environment = $environment;
        $instance->debug = (Boolean) $debug;
        $instance->container = $container;

        self::$_instance = $instance;

        return $instance;
    }

    public static function instance()
    {
        if (empty(self::$_instance)) {
            throw new \RuntimeException('ServiceKernel未实例化');
        }
        self::$_instance->boot();
        return self::$_instance;
    }

    public function boot()
    {
        if (true === $this->booted) {
            return;
        }
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    public function getRootPath()
    {
        return $this->rootPath;
    }

    public function createService($name)
    {
        if (empty($this->pool[$name])) {
            $namespace = substr(__NAMESPACE__, 0, -strlen('Common')-1);
            list($module, $className) = explode('.', $name);
            $class = $namespace . '\\' . $module. '\\Impl\\' . $className . 'Impl';
            $this->pool[$name] = new $class();
        }
        return $this->pool[$name];
    }

    public function createDao($name)
    {
        if (empty($this->pool[$name])) {
            $namespace = substr(__NAMESPACE__, 0, -strlen('Common')-1);
            list($module, $className) = explode('.', $name);
            $class = $namespace . '\\' . $module. '\\Dao\\Impl\\' . $className . 'Impl';
            $dao = new $class();
            $dao->setConnection($this->container->get('database_connection'));
            $this->pool[$name] = $dao;
        }
        return $this->pool[$name];
    }

}