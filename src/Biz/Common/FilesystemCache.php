<?php

namespace Biz\Common;

use Doctrine\Common\Cache\FilesystemCache as BaseFilesystemCache;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FilesystemCache extends BaseFilesystemCache
{
    public function __construct(ContainerInterface $container)
    {
        $kernel = $container->get('kernel');
        $dir = $kernel->getCacheDir().DIRECTORY_SEPARATOR.'twig_cache';
        parent::__construct($dir);
    }
}
