<?php
namespace Topxia\Service\Common;

use Doctrine\Common\Cache\FilesystemCache as BaseFilesystemCache;

class FilesystemCache extends BaseFilesystemCache
{
    public function __construct($container)
    {
        $kernel = $container->get('kernel');
        $dir = $kernel->getCacheDir().DIRECTORY_SEPARATOR.'twig_cache';
        parent::__construct($dir);
    }
}