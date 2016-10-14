<?php
namespace Topxia\Service\Common\Plugin;

use Pimple\Pimple;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PluginKernel extends Pimple
{
    protected $config;
    protected $serviceContainer;
    protected $registeredPlugins;

    public function __construct($config, $serviceContainer)
    {
        $this->config           = $config;
        $this->serviceContainer = $serviceContainer;
    }

    public function boot()
    {
        $this['event_dispatcher'] = function ($kernel) {
            return new EventDispatcher();
        };
    }

    public function getRegisteredPluginsCache()
    {
        // 插件管理可保存在数据库中，新插件安装时刷新该缓存文件
        if (is_null($this->registeredPlugins)) {
            return $this->registeredPlugins;
        }
        $this->registeredPlugins = include_once $this->config['registered_plugins_cache_file'];
        return $this->registeredPlugins;
    }

    public function updateRegisteredPluginsCache(array $registeredPlugins)
    {
        $this->registeredPlugins = null;

        //缓存内容为 code:大驼峰、ver:格式1.0.0、deps:插件的依赖
        $cache = "<?php \nreturn ".var_export($registeredPlugins, true).';';
        file_put_contents($this->config['registered_plugins_cache_file'], $cache);
    }

    public function registerPlugin(PluginInterface $plugin)
    {
        // 基本信息校验、检查依赖
        
        $plugin->registered();
    }

    public function unregisterPlugin(PluginInterface $plugin)
    {
        $plugin->unregistered();
    }

    public function enablePlugin(PluginInterface $plugin)
    {
        $plugin->enabled();
    }

    public function disablePlugin(PluginInterface $plugin)
    {
        $plugin->disabled();
    }

    public function placeHook($hookName, array $subject)
    {
        return $this->getEventDispatcher()->dispatch($hookName, new GenericEvent($subject));
    }

    public function addEventSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->getEventDispatcher()->addSubscriber($subscriber);
        return $this;
    }

    public function getEventDispatcher()
    {
        return $this['event_dispatcher'];
    }
}
