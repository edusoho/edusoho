<?php

namespace Biz\System\Util;

use Topxia\Service\Common\ServiceKernel;
use Biz\Course\Util\CourseTitleUtils;
use AppBundle\Common\PluginVersionToolkit;
use Symfony\Component\Yaml\Yaml;

class LogDataUtils
{
    public static function getYmlConfig()
    {
        $paths = self::getConfigPath();
        $permissions = array();
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                continue;
            }
            $menus = Yaml::parse(file_get_contents($path));
            if (empty($menus)) {
                continue;
            }

            $permissions = array_merge($permissions, $menus);
        }

        return $permissions;
    }

    private static function getConfigPath()
    {
        $configPaths = array();

        $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir');
        $files = array(
            $rootDir.'/../src/AppBundle/Resources/config/log_modules.yml',
            $rootDir.'/../src/CustomBundle/Resources/config/log_modules.yml',
        );

        foreach ($files as $filepath) {
            if (is_file($filepath)) {
                $configPaths[] = $filepath;
            }
        }

        $count = self::getAppService()->findAppCount();
        $apps = self::getAppService()->findApps(0, $count);

        foreach ($apps as $app) {
            if ('plugin' != $app['type']) {
                continue;
            }

            if ('MAIN' !== $app['code'] && $app['protocol'] < 3) {
                continue;
            }

            if (!PluginVersionToolkit::dependencyVersion($app['code'], $app['version'])) {
                continue;
            }

            $code = ucfirst($app['code']);
            $configPaths[] = "{$rootDir}/../plugins/{$code}Plugin/Resources/config/log_modules.yml";
        }

        return $configPaths;
    }

    public static function getLogDefaultConfig()
    {
        $config = array(
            '%title%' => array(
                'showTitle',
                'title',
                'courseSetTitle',
                'name',
                'content',
                'filename',
                'stem',
                'subject',
                'nickname',
            ),
        );

        return $config;
    }

    public static function getUnDisplayModuleAction()
    {
        $config = self::getYmlConfig();
        $returnActions = array();
        foreach ($config as $module => $actions) {
            foreach ($actions as $action => $format) {
                if (array_key_exists('notDisplay', $format) && 1 == $format['notDisplay']) {
                    $returnActions[] = $action;
                }
            }
        }

        return $returnActions;
    }

    public static function trans($message, $module, $action)
    {
        $prefixs = self::getTransPrefix($module, $action);
        foreach ($prefixs as $prefix) {
            $transMessage = $prefix.'.'.$message;
            $trans = ServiceKernel::instance()->trans($transMessage, array(), null, null);
            if ($trans != $transMessage) {
                return $trans;
            }
        }

        return $message;
    }

    public static function serializeChanges($oldData, $newData)
    {
        $newData = self::serializeUnsetChanges($newData);
        $oldData = self::initArray($oldData);
        $newData = self::initArray($newData);
        $changeFields = self::arrayChanges($oldData, $newData);
        if (empty($changeFields['before']) && empty($changeFields['after'])) {
            return array();
        }
        $config = array(
            'buyExpiryTime' => array(
                'timeConvent',
            ),
            'expiryStartDate' => array(
                'timeConvent',
            ),
            'expiryEndDate' => array(
                'timeConvent',
            ),
            'expiryValue' => array(
                'timeConvent',
            ),
            'cloud_consult_expired_time' => array(
                'timeConvent',
            ),
            'password' => array(
                'passwordSetBlank',
            ),
        );

        $changeFields = self::serializeData($config, $changeFields);

        $changeFields = self::getShowField($changeFields, $oldData);

        return $changeFields;
    }

    private static function initArray($array)
    {
        if (!is_array($array)) {
            $array = array($array);
        }

        return $array;
    }

    private static function arrayChanges(array $before = array(), array $after)
    {
        $changes = array('before' => array(), 'after' => array());

        foreach ($after as $key => $value) {
            if (!isset($before[$key])) {
                $before[$key] = '';
            }

            if ($value != $before[$key]) {
                $changes['before'][$key] = $before[$key];
                $changes['after'][$key] = $value;
            }
        }

        return $changes;
    }

    private static function serializeUnsetChanges($newData)
    {
        $config = array(
            'createdTime',
            'updatedTime',
            'updateTime',
            'coin_picture_50_50',
            'coin_picture_30_30',
            'coin_picture_20_20',
            'coin_picture_10_10',
        );
        foreach ($config as $value) {
            if (isset($newData[$value])) {
                unset($newData[$value]);
            }
        }

        return $newData;
    }

    private static function getShowField($changeFields, $oldData)
    {
        $changeFields['id'] = self::getShowId($oldData);
        $changeFields['showTitle'] = self::getShowTitle($oldData);

        return $changeFields;
    }

    private static function getShowId($oldData)
    {
        $showId = '';
        if (isset($oldData['id'])) {
            $showId = $oldData['id'];
        }

        return $showId;
    }

    private static function getShowTitle($oldData)
    {
        $showTitle = '';
        if (isset($oldData['nickname'])) {
            $showTitle = $oldData['nickname'];
        } elseif (isset($oldData['title'])) {
            $showTitle = $oldData['title'];
            if (isset($oldData['courseSetTitle'])) {
                $showTitle = CourseTitleUtils::getDisplayedTitle($oldData);
            }
        } elseif (isset($oldData['name'])) {
            $showTitle = $oldData['name'];
        }

        return $showTitle;
    }

    private static function serializeData($config, $changeFields)
    {
        $data = array();
        $oldData = $changeFields['before'];
        $newData = $changeFields['after'];

        foreach ($oldData as $key => $oldValue) {
            $newValue = $newData[$key];
            $old = $oldValue;
            $new = $newValue;

            if (isset($config[$key])) {
                foreach ($config[$key] as $function) {
                    $old = self::$function($oldValue);
                    $new = self::$function($newValue);
                }
            }

            $data[$key] = array(
                'old' => $old,
                'new' => $new,
            );
        }

        return $data;
    }

    public static function getTransPrefix($module, $action)
    {
        return array(
            'log.attr.'.$module.'.'.$action,
            'log.attr.'.$module,
            'log.attr',
        );
    }

    private static function timeConvent($time)
    {
        if ($time > 10000) {
            $time = date('Y-m-d H:i:s', $time);
        }

        return $time;
    }

    private static function passwordSetBlank($password)
    {
        if (!empty($password)) {
            $password = '******';
        }

        return $password;
    }

    private static function getAppService()
    {
        return self::getServiceKernel()->createService('CloudPlatform:AppService');
    }

    private static function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
