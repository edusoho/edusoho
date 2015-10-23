<?php

use Symfony\Component\Filesystem\Filesystem;

abstract class BaseInstallScript
{

    protected $meta;

    protected $kernel;

    protected $installMode = 'appstore';

    public function __construct ($kernel)
    {
        $this->kernel = $kernel;
        $this->meta = json_decode(file_get_contents(__DIR__ .'/../plugin.json'), true);
    }

    abstract public function install();

    public function execute()
    {
        $this->getConnection()->beginTransaction();
        try{
            $this->install();
            $this->installAssets();
            $this->getConnection()->commit();
        } catch(\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }
    }

    public function setInstallMode($mode)
    {
        if (!in_array($mode, array('appstore', 'command'))) {
            throw new \RuntimeException("$mode is not validate install mode.");
        }

        $this->installMode = $mode;
    }

    protected function installAssets()
    {
        $code = $this->meta['code'];

        $rootDir = realpath($this->kernel->getParameter('kernel.root_dir').'/../');
        
        $originDir = "{$rootDir}/plugins/{$code}/{$code}Bundle/Resources/public";
        if (!is_dir($originDir)) {
            return false;
        }

        $targetDir = "{$rootDir}/web/bundles/" . strtolower($code);

        $filesystem = new Filesystem();
        if ($filesystem->exists($targetDir)) {
            $filesystem->remove($targetDir);
        }

        if ($this->installMode == 'command') {
            $filesystem->symlink($originDir, $targetDir, true);
        } else {
            $filesystem->mirror($originDir, $targetDir, null, array('override' => true, 'delete' => true));
        }
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    protected function getConnection()
    {
        return $this->kernel->getConnection();
    }

}