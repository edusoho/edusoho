<?php
namespace Topxia\Common;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class PointcutToolkit
{
	public static function load($key)
	{
        $pointcutFile = ServiceKernel::instance()->getParameter('kernel.root_dir').'/cache/'.ServiceKernel::instance()->getEnvironment().'/ponitcut.php';

        $pointcuts = array();
        if (file_exists($pointcutFile)) {
            $pointcuts = include $pointcutFile;
        } else {
            $finder = new Finder();
            $finder->directories()->depth('== 0');

            foreach (ServiceKernel::instance()->getModuleDirectories() as $dir) {
                if (glob($dir.'/*/*/Resources', GLOB_ONLYDIR)) {
                    $finder->in($dir.'/*/*/Resources');
                }
            }

            foreach ($finder as $dir) {
                $filepath = $dir->getRealPath().'/pointcut.yml';
                if (file_exists($filepath)) {
                	$points = Yaml::parse($filepath);
                    $pointcuts = array_merge_recursive($pointcuts, $points);
                }
            }

            if (!ServiceKernel::instance()->isDebug()) {
                $cache = "<?php \nreturn ".var_export($pointcuts, true).';';
                file_put_contents($pointcutFile, $cache);
            }
        }

        return isset($pointcuts[$key])? $pointcuts[$key] : array();
    }

}