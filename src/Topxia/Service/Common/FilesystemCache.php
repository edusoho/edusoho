<?php
namespace Topxia\Service\Common;

use Doctrine\Common\Cache\FilesystemCache as BaseFilesystemCache;

class FilesystemCache extends BaseFilesystemCache
{

    public function __construct($dir)
    {
    	$environment = ServiceKernel::instance()->getEnvironment();
        $this->directory = $dir.DIRECTORY_SEPARATOR.$environment.DIRECTORY_SEPARATOR.'twig_cache';
    }

}