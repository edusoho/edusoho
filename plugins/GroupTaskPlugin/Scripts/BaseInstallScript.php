<?php

use Symfony\Component\Filesystem\Filesystem;

abstract class BaseInstallScript
{
    protected $meta;

    /**
     * @var \Codeages\Biz\Framework\Context\Biz
     */
    protected $biz;

    protected $installMode = 'appstore';

    public function __construct($biz)
    {
        $this->biz  = $biz;
        $this->meta = json_decode(file_get_contents(__DIR__.'/../plugin.json'), true);
    }

    abstract public function install();

    public function execute()
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->install();
            $this->installAssets();
            $this->getConnection()->commit();
        } catch (\Exception $e) {
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

        $rootDir = realpath($this->biz['root_directory']);

        $originDir = "{$rootDir}/plugins/{$code}Plugin/Resources/static-dist";
        if (!is_dir($originDir)) {
            return false;
        }

        $targetDir = "{$rootDir}/web/static-dist/".strtolower($code).'plugin';

        $filesystem = new Filesystem();
        if ($filesystem->exists($targetDir)) {
            $filesystem->remove($targetDir);
        }

        if ($this->installMode == 'command') {
            $filesystem->symlink($this->filesystem->makePathRelative($originDir, realpath(dirname($targetDir))), $targetDir, true);
        } else {
            $filesystem->mirror($originDir, $targetDir, null, array('override' => true, 'delete' => true));
        }
    }

    protected function createService($name)
    {
        return $this->biz->service($name);
    }

    protected function createDao($name)
    {
        return $this->biz->dao($name);
    }

    protected function getConnection()
    {
        return $this->biz['db'];
    }
}
