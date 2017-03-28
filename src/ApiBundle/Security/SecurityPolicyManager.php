<?php

namespace ApiBundle\Security;

use ApiBundle\Api\SecurityPolicy;
use ApiBundle\ApiBundle;
use Codeages\PluginBundle\System\PluginConfigurationManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class SecurityPolicyManager
{
    private $container;
    private $pluginConfigManager;
    private $whiteList;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->pluginConfigManager = new PluginConfigurationManager($container->getParameter('kernel.root_dir'));
    }

    public function isInWhiteList(Request $request)
    {
        $whiteList = $this->getWhiteList();

        if (!empty($matches = $whiteList[$request->getMethod()])) {
            $path = rtrim($request->getPathInfo(), '/');
            foreach ($matches as $whitePath) {
                $whitePath = str_replace('/', '\/', $whitePath);
                if (preg_match("/^{$whitePath}$/", $path)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getWhiteList()
    {
        if ($this->whiteList) {
            return $this->whiteList;
        }

        $this->whiteList = (new SecurityPolicy())->getWhiteList();
        $this->appendPrefix($this->whiteList, ApiBundle::API_PREFIX);
        $installedPlugins = $this->pluginConfigManager->getInstalledPlugins();
        foreach ($installedPlugins as $pluginConfig) {
            $pluginWL = $this->getPluginWhiteList($pluginConfig);
            $this->appendPrefix($pluginWL, ApiBundle::API_PREFIX.'/plugins/'.strtolower($pluginConfig['code']));
            $this->mergeWhiteList($pluginWL);
        }

        return $this->whiteList;
    }

    private function getPluginWhiteList($pluginConfig)
    {
        if ($pluginConfig['type'] == 'plugin') {
            $policyClass = ucfirst($pluginConfig['code']).'Plugin\\Api\\SecurityPolicy';
            if (class_exists($policyClass)) {
                return (new $policyClass())->getWhiteList();
            }
        }

        return array();
    }

    private function mergeWhiteList($newWL)
    {
        foreach ($newWL as $method => $pathList) {
            if (empty($this->whiteList[$method])) {
                $this->whiteList[$method] = $pathList;
            } else {
                $this->whiteList[$method] = array_merge($this->whiteList[$method],  $pathList);
            }
        }
    }

    private function appendPrefix(&$whiteList, $prefix)
    {
        foreach ($whiteList as $method => &$pathList) {
            foreach ($pathList as &$path) {
                $path = $prefix . $path;
            }
        }
    }
}