<?php
namespace Topxia\Service\Common;

use Symfony\Component\Finder\Finder;
use Topxia\Service\Common\Proxy\ProxyManager;
use Topxia\Service\Common\Redis\RedisFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ServiceKernel
{
    private static $_instance;

    private static $_dispatcher;

    protected $_moduleDirectories = array();

    protected $_moduleConfig = array();

    protected $environment;
    protected $debug;
    protected $booted;

    protected $parameterBag;

    protected $currentUser;

    protected $pool = array();

    protected $classMaps = array();

    public function getRedis($group = 'default')
    {
        $redisFactory = RedisFactory::instance($this);
        $redis        = $redisFactory->getRedis($group);

        if ($redis) {
            return $redis;
        }

        return false;
    }

    public static function create($environment, $debug)
    {
        if (self::$_instance) {
            return self::$_instance;
        }

        $instance              = new self();
        $instance->environment = $environment;
        $instance->debug       = (Boolean) $debug;
        $instance->registerModuleDirectory(realpath(__DIR__.'/../../../'));

        self::$_instance = $instance;

        return $instance;
    }

    /**
     * @return ServiceKernel
     */
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

        $this->booted = true;

        $moduleConfigCacheFile = $this->getParameter('kernel.root_dir').'/cache/'.$this->environment.'/modules_config.php';

        if (file_exists($moduleConfigCacheFile)) {
            $this->_moduleConfig = include $moduleConfigCacheFile;
        } else {
            $finder = new Finder();
            $finder->directories()->depth('== 0');

            foreach ($this->_moduleDirectories as $dir) {
                if (glob($dir.'/*/Service', GLOB_ONLYDIR)) {
                    $finder->in($dir.'/*/Service');
                }
            }

            foreach ($finder as $dir) {
                $filepath = $dir->getRealPath().'/module_config.php';

                if (file_exists($filepath)) {
                    $this->_moduleConfig = array_merge_recursive($this->_moduleConfig, include $filepath);
                }
            }

            if (!$this->debug) {
                $cache = "<?php \nreturn ".var_export($this->_moduleConfig, true).';';
                file_put_contents($moduleConfigCacheFile, $cache);
            }
        }

        $subscribers = empty($this->_moduleConfig['event_subscriber']) ? array() : $this->_moduleConfig['event_subscriber'];

        foreach ($subscribers as $subscriber) {
            $this->dispatcher()->addSubscriber(new $subscriber());
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

    public function hasParameter($name)
    {
        if (is_null($this->parameterBag)) {
            throw new \RuntimeException('尚未初始化ParameterBag');
        }

        return $this->parameterBag->has($name);
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

    public function setEnvVariable(array $env)
    {
        $this->env = $env;
        return $this;
    }

    public function getEnvVariable($key = null)
    {
        if (empty($key)) {
            return $this->env;
        }

        if (!isset($this->env[$key])) {
            throw new \RuntimeException("Environment variable `{$key}` is not exist.");
        }

        return $this->env[$key];
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

    //思考:createService的逻辑是否应该放到ProxyManager里？单一职责还是最少知道原则？
    public function createService($name)
    {
        if (empty($this->pool[$name])) {
            $class = $this->getClassName('service', $name);

            $this->pool[$name] = ProxyManager::create($class);
        }

        return $this->pool[$name];
    }

    public function createDao($name)
    {
        if (empty($this->pool[$name])) {
            $class = $this->getClassName('dao', $name);
            $dao   = ProxyManager::create($class);
            $dao->setConnection($this->getConnection());
            $dao->setRedis($this->getRedis());
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

    public function registerModuleDirectory($dir)
    {
        $this->_moduleDirectories[] = $dir;
    }

    public function getModuleDirectories()
    {
        return $this->_moduleDirectories;
    }

    public function getModuleConfig($key, $default = null)
    {
        if (!isset($this->_moduleConfig[$key])) {
            return $default;
        }

        return $this->_moduleConfig[$key];
    }

    protected function getClassName($type, $name)
    {
        $classMap = $this->getClassMap($type);

        if (isset($classMap[$name])) {
            return $classMap[$name];
        }

        if (strpos($name, ':') > 0) {
            list($namespace, $name) = explode(':', $name, 2);
            $namespace .= '\\Service';
        } else {
            $namespace = substr(__NAMESPACE__, 0, -strlen('Common') - 1);
        }

        list($module, $className) = explode('.', $name);

        $type = strtolower($type);

        if ($type == 'dao') {
            return $namespace.'\\'.$module.'\\Dao\\Impl\\'.$className.'Impl';
        }

        return $namespace.'\\'.$module.'\\Impl\\'.$className.'Impl';
    }

    protected function getClassMap($type)
    {
        if (isset($this->classMaps[$type])) {
            return $this->classMaps[$type];
        }

        $key = ($type == 'dao') ? 'topxia_daos' : 'topxia_services';

        if (!$this->hasParameter($key)) {
            $this->classMaps[$type] = array();
        } else {
            $this->classMaps[$type] = $this->getParameter($key);
        }

        return $this->classMaps[$type];
    }
}
