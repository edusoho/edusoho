<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Common\BlockToolkit;

class InstallScript
{
    protected $biz;

    public function __construct ($biz)
    {
        $this->biz = $biz;
    }

    public function execute()
    {
        $filesystem = new Filesystem();
        $originDir = $this->biz['kernel.root_dir'] . '/../web/themes/{{name}}/static-dist/{{name}}theme';
        $distDir = $this->biz['kernel.root_dir'] . '/../web/static-dist/{{name}}theme';
        $filesystem->mirror($originDir, $distDir, null, array(
        'override' => true,
        'delete' => true,
        ));
    }

    public function setInstallMode($mode)
    {
        if (!in_array($mode, array('appstore', 'command'))) {
            throw new \RuntimeException("$mode is not validate install mode.");
        }

        $this->installMode = $mode;
    }

    protected function createDao($alias)
    {
        return $this->biz->dao($alias);
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getThemeConfigDao()
    {
        return $this->createDao('Theme:ThemeConfigDao');
    }

    protected function getThemeService()
    {
        return $this->createService('Theme:ThemeService');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    protected function getAppDao()
    {
        return $this->createDao('CloudPlatform:CloudAppDao');
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }
}