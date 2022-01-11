<?php

namespace AppBundle\Extension;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ActivityExtension extends Extension implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $activityDir = $container['activity_dir'];
        $activitiesDir = glob($activityDir.'/*', GLOB_ONLYDIR);

        foreach ($activitiesDir as $dir) {
            $pathInfo = pathinfo($dir);
            $type = $pathInfo['filename'];
            $activityExtFile = implode(DIRECTORY_SEPARATOR, [$dir, "activity_{$type}.php"]);
            if (file_exists($activityExtFile)) {
                require_once $activityExtFile;
                $class = "activity_{$type}";
                $customExt = new $class($container);
                if ($customExt instanceof Activity) {
                    $container['activity_type.'.$type] = $customExt;
                }
            } else {
                $activities = $this->getActivities();
                $activity = empty($activities[$type]) ? null : $activities[$type];
                $container['activity_type.'.$type] = isset($activity['typeClass']) ? function ($biz) use ($activity) {return new $activity['typeClass']($biz); }
                : new Activity($container);
            }
        }
    }

    public function getActivities()
    {
        $biz = $this->biz;

        return [
            'text' => [
                'meta' => [
                    'name' => 'course.activity.text',
                    'icon' => 'es-icon es-icon-graphic',
                ],
                'typeClass' => '\Biz\Activity\Type\Text',
                'controller' => 'AppBundle:Activity/Text',
                'canFree' => true,
                'visible' => function ($courseSet, $course) {
                    return 'live' != $courseSet['type'];
                },
            ],
            'video' => [
                'meta' => [
                    'name' => 'course.activity.video',
                    'icon' => 'es-icon es-icon-video',
                ],
                'typeClass' => '\Biz\Activity\Type\Video',
                'controller' => 'AppBundle:Activity/Video',
                'canFree' => true,
                'visible' => function ($courseSet, $course) {
                    return 'live' != $courseSet['type'];
                },
            ],
            'audio' => [
                'meta' => [
                    'name' => 'course.activity.audio',
                    'icon' => 'es-icon es-icon-audio',
                ],
                'typeClass' => '\Biz\Activity\Type\Audio',
                'controller' => 'AppBundle:Activity/Audio',
                'canFree' => true,
                'visible' => function ($courseSet, $course) {
                    return 'live' != $courseSet['type'];
                },
            ],
            'live' => [
                'meta' => [
                    'name' => 'course.activity.live',
                    'icon' => 'es-icon es-icon-entry-live',
                ],
                'typeClass' => '\Biz\Activity\Type\Live',
                'controller' => 'AppBundle:Activity/Live',
                'canFree' => false,
                'visible' => function ($courseSet, $course) use ($biz) {
                    $storage = $biz->service('System:SettingService')->get('course');

                    return ArrayToolkit::get($storage, 'live_course_enabled', false);
                },
            ],
            'discuss' => [
                'meta' => [
                    'name' => 'course.activity.discuss',
                    'icon' => 'es-icon es-icon-discuss',
                ],
                'typeClass' => '\Biz\Activity\Type\Discuss',
                'controller' => 'AppBundle:Activity/Discuss',
                'canFree' => false,
                'visible' => function ($courseSet, $course) {
                    return true;
                },
            ],

            'flash' => [
                'meta' => [
                    'name' => 'course.activity.flash',
                    'icon' => 'es-icon es-icon-flash',
                ],
                'typeClass' => '\Biz\Activity\Type\Flash',
                'controller' => 'AppBundle:Activity/Flash',
                'canFree' => true,
                'visible' => function ($courseSet, $course) use ($biz) {
                    $storage = $biz->service('System:SettingService')->get('storage');
                    $uploadMode = ArrayToolkit::get($storage, 'upload_mode', 'local');

                    return 'cloud' == $uploadMode && 'live' != $courseSet['type'];
                },
            ],
            'doc' => [
                'meta' => [
                    'name' => 'course.activity.doc',
                    'icon' => 'es-icon es-icon-document',
                ],
                'typeClass' => '\Biz\Activity\Type\Doc',
                'controller' => 'AppBundle:Activity/Doc',
                'canFree' => true,
                'visible' => function ($courseSet, $course) use ($biz) {
                    $storage = $biz->service('System:SettingService')->get('storage');
                    $uploadMode = ArrayToolkit::get($storage, 'upload_mode', 'local');

                    return 'cloud' == $uploadMode && 'live' != $courseSet['type'];
                },
            ],
            'ppt' => [
                'meta' => [
                    'name' => 'course.activity.ppt',
                    'icon' => 'es-icon es-icon-ppt',
                ],
                'typeClass' => '\Biz\Activity\Type\Ppt',
                'controller' => 'AppBundle:Activity/Ppt',
                'canFree' => true,
                'visible' => function ($courseSet, $course) use ($biz) {
                    $storage = $biz->service('System:SettingService')->get('storage');
                    $uploadMode = ArrayToolkit::get($storage, 'upload_mode', 'local');

                    return 'cloud' == $uploadMode && 'live' != $courseSet['type'];
                },
            ],
            'testpaper' => [
                'meta' => [
                    'name' => 'course.activity.testpaper',
                    'icon' => 'es-icon es-icon-examination',
                ],
                'typeClass' => '\Biz\Activity\Type\Testpaper',
                'controller' => 'AppBundle:Activity/Testpaper',
                'canFree' => false,
                'visible' => function ($courseSet, $course) {
                    return true;
                },
            ],
            'homework' => [
                'meta' => [
                    'name' => 'course.activity.homework',
                    'icon' => 'es-icon es-icon-task',
                ],
                'typeClass' => '\Biz\Activity\Type\Homework',
                'controller' => 'AppBundle:Activity/Homework',
                'canFree' => false,
                'visible' => function ($courseSet, $course) {
                    return true;
                },
            ],
            'exercise' => [
                'meta' => [
                    'name' => 'course.activity.exercise',
                    'icon' => 'es-icon es-icon-exercise',
                ],
                'typeClass' => '\Biz\Activity\Type\Exercise',
                'controller' => 'AppBundle:Activity/Exercise',
                'canFree' => false,
                'visible' => function ($courseSet, $course) {
                    return true;
                },
            ],
            'download' => [
                'meta' => [
                    'name' => 'course.activity.download',
                    'icon' => 'es-icon es-icon-downloadfile',
                ],
                'typeClass' => '\Biz\Activity\Type\Download',
                'controller' => 'AppBundle:Activity/Download',
                'canFree' => false,
                'visible' => function ($courseSet, $course) {
                    return true;
                },
            ],
            'replay' => [
                'meta' => [
                    'name' => 'course.activity.replay',
                    'icon' => 'es-icon es-icon-zhibohuifang',
                ],
                'typeClass' => '\Biz\Activity\Type\Replay',
                'controller' => 'AppBundle:Activity/Replay',
                'canFree' => true,
                'visible' => function ($courseSet, $course) {
                    return true;
                },
            ],
        ];
    }
}
