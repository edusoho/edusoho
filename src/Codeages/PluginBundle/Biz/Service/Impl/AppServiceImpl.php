<?php
namespace Codeages\PluginBundle\Biz\Service\Impl;

use Codeages\PluginBundle\Biz\Service\AppService;
use Codeages\Biz\Framework\Service\BaseService;
use Symfony\Component\Yaml\Yaml;

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

    public function installPlugin($code)
    {
        $code = ucfirst($code);

        $exist = $this->getAppByCode($code);
        if ($exist) {
            throw new \RuntimeException("Plugin `{$code}` is already installed.");
        }

        $metaFile = $this->biz['plugin.directory'] . DIRECTORY_SEPARATOR . "{$code}Plugin" . DIRECTORY_SEPARATOR . 'plugin.json';
        if (!file_exists($metaFile)) {
            throw new \RuntimeException('Plugin meta file(plugin.json) is not exist.');
        }

        $meta = json_decode(file_get_contents($metaFile), true);

        if ($meta['code'] !== $code) {
            throw new \RuntimeException("Plugin meta code value `{$meta['code']}` is invalid, please keep this value equal to `{$code}`.");
        }

        $app = array();

        $app['type'] = 'plugin';
        $app['code'] = $meta['code'];
        $app['name'] = $meta['name'];
        $app['description'] = $meta['description'];
        $app['author'] = $meta['author'];
        $app['version'] = $meta['version'];

        $app = $this->getAppDao()->create($app);

        $this->rebuildPlugins();
    }

    public function removePlugin($code)
    {
        $code = ucfirst($code);

        $app = $this->getAppByCode($code);
        if (!$app) {
            throw new \RuntimeException("Plugin `{$code}` is not installed.");
        }

        $this->getAppDao()->delete($app['id']);

        $this->rebuildPlugins();
    }

    public function rebuildPlugins()
    {
        $plugins = $this->findAllPlugins();

        $config = array();
        foreach ($plugins as $plugin) {
            $config[] = array(
                'code' => $plugin['code'],
                'version' => $plugin['version'],
                'type' => $plugin['type'],
            );
        }

        $content = "<?php \n return " . var_export($config, true) . ";";
        file_put_contents($this->biz['plugin.config_file'], $content);
    }

    protected function getAppDao()
    {
        return $this->biz->dao('CodeagesPluginBundle:AppDao');
    }

}