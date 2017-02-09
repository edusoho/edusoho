<?php
namespace Topxia\Common;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use Topxia\Service\Common\ServiceKernel;

class JoinPointToolkit
{
    public static function load($key)
    {
        $pointcutFile = ServiceKernel::instance()->getParameter('kernel.root_dir').'/cache/'.ServiceKernel::instance()->getEnvironment().'/join_point.php';

        $joinPoints = array();
        if (file_exists($pointcutFile)) {
            $joinPoints = include $pointcutFile;
        } else {
            $finder = new Finder();
            $finder->directories()->depth('== 0');

            foreach (ServiceKernel::instance()->getModuleDirectories() as $dir) {
                if (glob($dir.'/*/*/Resources', GLOB_ONLYDIR)) {
                    $finder->in($dir.'/*/*/Resources');
                }
            }

            foreach ($finder as $dir) {
                $filepath = $dir->getRealPath().'/join_point.yml';
                if (file_exists($filepath)) {
                    $points     = Yaml::parse(file_get_contents($filepath));
                    $joinPoints = array_merge_recursive($joinPoints, $points);
                }
            }

            if (!ServiceKernel::instance()->isDebug()) {
                $cache = "<?php \nreturn ".var_export($joinPoints, true).';';
                file_put_contents($pointcutFile, $cache);
            }
        }

        return isset($joinPoints[$key]) ? $joinPoints[$key] : array();
    }
}
