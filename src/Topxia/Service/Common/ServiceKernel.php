<?php
namespace Topxia\Service\Common;

use Symfony\Component\EventDispatcher\EventDispatcher;

class ServiceKernel
{

    private static $_instance;

    private static $_dispatcher;

    protected $environment;
    protected $debug;
    protected $booted;

    protected $parameterBag;

    protected $currentUser;

    protected $pool = array();

    public static function create($environment, $debug)
    {
        if (self::$_instance) {
            return self::$_instance;
        }

        $instance = new self();
        $instance->environment = $environment;
        $instance->debug = (Boolean) $debug;

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

    public static function dispatcher()
    {
        if (self::$_dispatcher) {
            return self::$_dispatcher;
        }

        self::$_dispatcher = new EventDispatcher();

        return self::$_dispatcher;
    }

    public function boot()
    {
        if (true === $this->booted) {
            return;
        }
    }

    public function setParameterBag($parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function getParameter($name)
    {
        if (is_null($this->parameterBag)) {
            throw new \RuntimeException('尚未初始化ParameterBag');
        }
        return $this->parameterBag->get($name);
    }

    public function setCurrentUser($currentUser)
    {
        $this->currentUser = $currentUser;
        return $this;
    }

    public function getCurrentUser()
    {
        if (is_null($this->currentUser)) {
            throw new \RuntimeException('尚未初始化CurrentUser');
        }
        return $this->currentUser;
    }

    public function getConnection()
    {
        if (is_null($this->connection)) {
            throw new \RuntimeException('尚未初始化数据库连接');
        }
        return $this->connection;
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }

    public function createService($name)
    {
        if (empty($this->pool[$name])) {
            $class = $this->getClassName('service', $name);
            $this->pool[$name] = new $class();
        }
        return $this->pool[$name];
    }

    public function createDao($name)
    {
        if (empty($this->pool[$name])) {
            $class = $this->getClassName('dao', $name);
            $dao = new $class();
            $dao->setConnection($this->getConnection());
            $this->pool[$name] = $dao;
        }
        return $this->pool[$name];
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function isDebug()
    {
        return $this->debug;
    }

    private function getClassName($type, $name)
    {
        if (strpos($name, ':') > 0) {
            list($namespace, $name) = explode(':', $name, 2);
            $namespace .= '\\Service';
        } else {
            $namespace = substr(__NAMESPACE__, 0, -strlen('Common')-1);
        }
        list($module, $className) = explode('.', $name);

        $type = strtolower($type);
        if ($type == 'dao') {
            return $namespace . '\\' . $module. '\\Dao\\Impl\\' . $className . 'Impl';
        }
        return $namespace . '\\' . $module. '\\Impl\\' . $className . 'Impl';
    }

}