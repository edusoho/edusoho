<?php

namespace AppBundle\Extension;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Type\Audio;
use Biz\Activity\Type\Discuss;
use Biz\Activity\Type\Doc;
use Biz\Activity\Type\Download;
use Biz\Activity\Type\Exercise;
use Biz\Activity\Type\Flash;
use Biz\Activity\Type\Homework;
use Biz\Activity\Type\Live;
use Biz\Activity\Type\Ppt;
use Biz\Activity\Type\Testpaper;
use Biz\Activity\Type\Text;
use Biz\Activity\Type\Video;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ActivityExtension extends Extension implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['activity_type.text'] = function ($biz) {
            return new Text($biz);
        };
        $container['activity_type.video'] = function ($biz) {
            return new Video($biz);
        };

        $container['activity_type.audio'] = function ($biz) {
            return new Audio($biz);
        };

        $container['activity_type.download'] = function ($biz) {
            return new Download($biz);
        };

        $container['activity_type.live'] = function ($biz) {
            return new Live($biz);
        };

        $container['activity_type.discuss'] = function ($biz) {
            return new Discuss($biz);
        };

        $container['activity_type.flash'] = function ($biz) {
            return new Flash($biz);
        };

        $container['activity_type.doc'] = function ($biz) {
            return new Doc($biz);
        };

        $container['activity_type.ppt'] = function ($biz) {
            return new Ppt($biz);
        };
        $container['activity_type.testpaper'] = function ($biz) {
            return new Testpaper($biz);
        };
        $container['activity_type.homework'] = function ($biz) {
            return new Homework($biz);
        };
        $container['activity_type.exercise'] = function ($biz) {
            return new Exercise($biz);
        };
    }

    public function getActivities()
    {
        $biz = $this->biz;

        return array(
            'text' => array(
                'meta' => array(
                    'name' => 'course.activity.text',
                    'icon' => 'es-icon es-icon-graphicclass',
                ),
                'controller' => 'AppBundle:Activity/Text',
                'canFree' => true,
                'visible' => function ($courseSet, $course) {
                    return $courseSet['type'] != 'live';
                },
            ),
            'video' => array(
                'meta' => array(
                    'name' => 'course.activity.video',
                    'icon' => 'es-icon es-icon-videoclass',
                ),
                'controller' => 'AppBundle:Activity/Video',
                'canFree' => true,
                'visible' => function ($courseSet, $course) {
                    return $courseSet['type'] != 'live';
                },
            ),
            'audio' => array(
                'meta' => array(
                    'name' => 'course.activity.audio',
                    'icon' => 'es-icon es-icon-audioclass',
                ),
                'controller' => 'AppBundle:Activity/Audio',
                'canFree' => true,
                'visible' => function ($courseSet, $course) {
                    return $courseSet['type'] != 'live';
                },
            ),
            'live' => array(
                'meta' => array(
                    'name' => 'course.activity.live',
                    'icon' => 'es-icon es-icon-videocam',
                ),
                'controller' => 'AppBundle:Activity/Live',
                'canFree' => false,
                'visible' => function ($courseSet, $course) use ($biz) {
                    $storage = $biz->service('System:SettingService')->get('course');

                    return ArrayToolkit::get($storage, 'live_course_enabled', false);
                },
            ),
            'discuss' => array(
                'meta' => array(
                    'name' => 'course.activity.discuss',
                    'icon' => 'es-icon es-icon-comment',
                ),
                'controller' => 'AppBundle:Activity/Discuss',
                'canFree' => false,
                'visible' => function ($courseSet, $course) {
                    return true;
                },
            ),

            'flash' => array(
                'meta' => array(
                    'name' => 'course.activity.flash',
                    'icon' => 'es-icon es-icon-flashclass',
                ),
                'controller' => 'AppBundle:Activity/Flash',
                'canFree' => true,
                'visible' => function ($courseSet, $course) use ($biz) {
                    $storage = $biz->service('System:SettingService')->get('storage');
                    $uploadMode = ArrayToolkit::get($storage, 'upload_mode', 'local');

                    return $uploadMode == 'cloud' && $courseSet['type'] != 'live';
                },
            ),
            'doc' => array(
                'meta' => array(
                    'name' => 'course.activity.doc',
                    'icon' => 'es-icon es-icon-description',
                ),
                'controller' => 'AppBundle:Activity/Doc',
                'canFree' => true,
                'visible' => function ($courseSet, $course) use ($biz) {
                    $storage = $biz->service('System:SettingService')->get('storage');
                    $uploadMode = ArrayToolkit::get($storage, 'upload_mode', 'local');

                    return $uploadMode == 'cloud' && $courseSet['type'] != 'live';
                },
            ),
            'ppt' => array(
                'meta' => array(
                    'name' => 'course.activity.ppt',
                    'icon' => 'es-icon es-icon-pptclass',
                ),
                'controller' => 'AppBundle:Activity/Ppt',
                'canFree' => true,
                'visible' => function ($courseSet, $course) use ($biz) {
                    $storage = $biz->service('System:SettingService')->get('storage');
                    $uploadMode = ArrayToolkit::get($storage, 'upload_mode', 'local');

                    return $uploadMode == 'cloud' && $courseSet['type'] != 'live';
                },
            ),
            'testpaper' => array(
                'meta' => array(
                    'name' => 'course.activity.testpaper',
                    'icon' => 'es-icon es-icon-kaoshi',
                ),
                'controller' => 'AppBundle:Activity/Testpaper',
                'canFree' => false,
                'visible' => function ($courseSet, $course) use ($biz) {
                    return true;
                },
            ),
            'homework' => array(
                'meta' => array(
                    'name' => 'course.activity.homework',
                    'icon' => 'es-icon es-icon-zuoye',
                ),
                'controller' => 'AppBundle:Activity/Homework',
                'canFree' => false,
                'visible' => function ($courseSet, $course) use ($biz) {
                    return true;
                },
            ),
            'exercise' => array(
                'meta' => array(
                    'name' => 'course.activity.exercise',
                    'icon' => 'es-icon es-icon-mylibrarybooks',
                ),
                'controller' => 'AppBundle:Activity/Exercise',
                'canFree' => false,
                'visible' => function ($courseSet, $course) use ($biz) {
                    return true;
                },
            ),
            'download' => array(
                'meta' => array(
                    'name' => 'course.activity.download',
                    'icon' => 'es-icon es-icon-filedownload',
                ),
                'controller' => 'AppBundle:Activity/Download',
                'canFree' => false,
                'visible' => function ($courseSet, $course) {
                    return true;
                },
            ),
        );
    }
}
