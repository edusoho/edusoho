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

        if (('upgrade' == $this->upgradeType) && empty($this->upgradeVersion)) {
            throw new \RuntimeException("Upgrade version `{$this->upgradeVersion}` is empty");
        }

        $method = "{$this->upgradeType}Update";

        $this->$method();
    }

    protected function installUpdate()
    {
        $scriptFilePath = __DIR__.'/Scripts/InstallScript.php';
        if (file_exists($scriptFilePath)) {
            include $scriptFilePath;
            $updater = new \InstallScript($this->kernel);
            $updater->execute();
        }
        $this->copyStaticFile();
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
        if (file_exists($scriptFilePath)) {
            include $scriptFilePath;
            $className = "\\{$className}";
            $updater = new $className($this->kernel, $this->upgradeVersion);
            $updater->execute();
        }

        $this->copyStaticFile();
    }

    private function copyStaticFile()
    {
        $rootDir = realpath($this->kernel['root_directory']);
        $code = '{{code}}';
        $lowerCode = strtolower($code);

        $filesystem = new Filesystem();
        $originDir = "{$rootDir}/plugins/{$code}Plugin/Resources/public";
        if (is_dir($originDir)) {
            $targetDir = "{$rootDir}/web/bundles/{$lowerCode}plugin";
            if ($filesystem->exists($targetDir)) {
                $filesystem->remove($targetDir);
            }
            $filesystem->mirror($originDir, $targetDir, null, array('override' => true, 'delete' => true));
        }

        $originDir = "{$rootDir}/plugins/{$code}Plugin/Resources/static-dist/{$lowerCode}plugin";
        $targetDir = "{$rootDir}/web/static-dist/{$lowerCode}plugin";

        if (is_dir($originDir)) {
            if ($filesystem->exists($targetDir)) {
                $filesystem->remove($targetDir);
            }

            $filesystem->mirror($originDir, $targetDir, null, array('override' => true, 'delete' => true));
        }
    }

    public function setUpgradeType($type, $version = null)
    {
        $this->upgradeType = strtolower($type);
        $this->upgradeVersion = $version;
    }
}
