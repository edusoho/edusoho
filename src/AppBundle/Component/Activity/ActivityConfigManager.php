<?php

namespace AppBundle\Component\Activity;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;

class ActivityConfigManager
{
    private $cachePath;

    private $activitiesConfig;

    public function __construct($cacheDir, $activitiesRootDir, $isDebug)
    {
        $this->cachePath = implode(DIRECTORY_SEPARATOR, array($cacheDir, 'activities.php'));
        $activitiesConfig = new ConfigCache($this->cachePath, $isDebug);

        if (!$activitiesConfig->isFresh()) {
            $this->reGenerate($activitiesConfig, $activitiesRootDir);
        }

        $this->activitiesConfig = require $this->cachePath;
    }

    private function reGenerate(ConfigCache $activitiesConfig, $activitiesRootDir)
    {
        $activitiesDir = glob($activitiesRootDir.'/*', GLOB_ONLYDIR);
        $resources = array();
        $code = array();
        foreach ($activitiesDir as $activityDir) {
            $pathInfo = pathinfo($activityDir);
            $activityJson = implode(DIRECTORY_SEPARATOR, array($activitiesRootDir, $pathInfo['filename'], 'activity.json'));

            if (!file_exists($activityJson)) {
                continue;
            }

            $jsonArr = json_decode(file_get_contents($activityJson), true);
            if ($jsonArr) {
                $resources[] = new FileResource($activityJson);
                $jsonArr['dir'] = implode(DIRECTORY_SEPARATOR, array($activitiesRootDir, $pathInfo['filename']));
                $code[$pathInfo['filename']] = $jsonArr;
            }
        }

        if (!$code) {
            $content = "<?php \n return array();";
        } else {
            $content = "<?php \n return ".var_export($code, true).';';
        }

        $activitiesConfig->write($content, $resources);
    }

    public function getInstalledActivity($type)
    {
        return empty($this->activitiesConfig[$type]) ? null : $this->activitiesConfig[$type];
    }

    public function isLtcActivity($type)
    {
        return empty($this->activitiesConfig[$type]) ? false : true;
    }

    public function getInstalledActivities()
    {
        return $this->activitiesConfig;
    }
}
