<?php

namespace TrainingTaskPlugin\Component\Activity;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;

class ActivityConfigManager
{
    private $cachePath;

    private $activitiesConfig;

    public function __construct($cacheDir, $activitiesRootDir, $isDebug)
    {
        $activitiesRootDir = $activitiesRootDir . "TrainingTaskPlugin";
        $this->cachePath = implode(DIRECTORY_SEPARATOR, array($cacheDir, 'plugin_training.php'));
        $activitiesConfig = new ConfigCache($this->cachePath, $isDebug);

        if (!$activitiesConfig->isFresh()) {
            $this->reGenerate($activitiesConfig, $activitiesRootDir);
        }

        $this->activitiesConfig = require $this->cachePath;
    }

    private function reGenerate(ConfigCache $activitiesConfig, $activitiesRootDir)
    {
        $activityJson = implode(DIRECTORY_SEPARATOR, array($activitiesRootDir, 'plugin.json'));

        if (!file_exists($activityJson)) {
            return;
        }

        $jsonArr = json_decode(file_get_contents($activityJson), true);
        if ($jsonArr) {
            $resources[] = new FileResource($activityJson);
            $jsonArr['dir'] = implode(DIRECTORY_SEPARATOR, array($activitiesRootDir));
            $code['training'] = $jsonArr;
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
