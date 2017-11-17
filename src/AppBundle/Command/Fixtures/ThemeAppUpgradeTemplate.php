<?php

use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\BlockToolkit;

class EduSohoPluginUpgrade
{
    protected $kernel;

    protected $upgradeType;

    protected $version;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function update()
    {
        if (empty($this->upgradeType)) {
            throw new \RuntimeException('Upgrade type is empty.');
        }

        if (!in_array($this->upgradeType, array('install', 'upgrade'))) {
            throw new \RuntimeException("Upgrade type `{$this->upgradeType}` is error.");
        }

        if (($this->upgradeType == 'upgrade') && empty($this->upgradeVersion)) {
            throw new \RuntimeException("Upgrade version `{$this->upgradeVersion}` is empty");
        }

        $method = "{$this->upgradeType}Update";

        $this->$method();
    }

    protected function installUpdate()
    {
        $this->copyStaticDist();
        $this->initBlock();
    }

    protected function initBlock()
    {
        BlockToolkit::init(__DIR__.'/block.json', null, __DIR__.'/blocks/');
    }

    protected function upgradeUpdate()
    {
        $className = 'UpgradeScript'.str_replace('.', '', $this->upgradeVersion);
        $scriptFilePath = __DIR__.'/Scripts/'.$className.'.php';
        $this->copyStaticDist();

        if (file_exists($scriptFilePath)) {
            include $scriptFilePath;
            $className = "\\{$className}";
            $updater = new $className($this->kernel, $this->upgradeVersion);
            $updater->execute();
        }
    }

    protected function copyStaticDist()
    {
        $filesystem = new Filesystem();
        $originDir = $this->kernel['theme.directory'].'/{{name}}/static-dist/{{name}}theme';
        $distDir = $this->kernel['theme.directory'].'/../static-dist/{{name}}theme';
        $filesystem->mirror($originDir, $distDir, null, array(
            'override' => true,
            'delete' => true,
        ));
    }

    public function setUpgradeType($type, $version = null)
    {
        $this->upgradeType = strtolower($type);
        $this->upgradeVersion = $version;
    }
}
