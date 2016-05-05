<?php
namespace Topxia\Service\Common;

use Doctrine\Common\Cache\FilesystemCache as BaseFilesystemCache;

class FilesystemCache extends BaseFilesystemCache
{
	private $dir;

    public function __construct($dir)
    {
    	$environment = ServiceKernel::instance()->getEnvironment();
        $this->dir = $dir.DIRECTORY_SEPARATOR.$environment.DIRECTORY_SEPARATOR.'twig_cache';
    }

}