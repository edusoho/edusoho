<?php

namespace Codeages\PluginBundle\Biz\Service\Impl;

use Codeages\PluginBundle\Biz\Service\AppService;
use Codeages\Biz\Framework\Service\BaseService;

class AppServiceImpl extends BaseService implements AppService
{
    public function getApp($id)
    {
        return $this->getAppDao()->get($id);
    }

    public function getAppByCode($code)
    {
        return $this->getAppDao()->getByCode($code);
    }

    public function findAllPlugins()
    {
        $total = $this->getAppDao()->countByType('plugin');
        if (empty($total)) {
            return array();
        }

        return $this->getAppDao()->findByType('plugin', 0, $total);
    }

    public function registerPlugin($plugin)
    {
        $app = array();

        $app['type'] = 'plugin';
        $app['code'] = $plugin['code'];
        $app['name'] = $plugin['name'];
        $app['description'] = $plugin['description'];
        $app['icon'] = '';
        // $app['author'] = $plugin['author'];
        $app['developerName'] = $plugin['author'];
        $app['version'] = $plugin['version'];
        $app['protocol'] = empty($plugin['protocol']) ? 3 : $plugin['protocol'];

        $app = $this->getAppDao()->create($app);

        return $app;
    }

    public function removePlugin($code)
    {
        $code = ucfirst($code);

        $app = $this->getAppByCode($code);
        if (!$app) {
            throw new \RuntimeException("Plugin `{$code}` is not installed.");
        }

        $this->getAppDao()->delete($app['id']);
    }

    protected function getAppDao()
    {
        return $this->biz->dao('CodeagesPluginBundle:OldAppDao');
    }
}
