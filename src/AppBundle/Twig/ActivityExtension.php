<?php

namespace AppBundle\Twig;

use Biz\Activity\Service\ActivityService;
use Biz\MaterialLib\Service\MaterialLibService;
use Biz\Player\Service\PlayerService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActivityExtension extends \Twig_Extension
{
    /**
     * @var Biz
     */
    protected $biz;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('activity_length_format', [$this, 'lengthFormat']),
            new \Twig_SimpleFilter('convert_minute_and_second', [$this, 'convertMinuteAndSecond']),
            new \Twig_SimpleFilter('prepare_video_media_uri', [$this, 'prepareVideoMediaUri']),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('activity_meta', [$this, 'getActivityMeta']),
            new \Twig_SimpleFunction('activity_metas', [$this, 'getActivityMeta']),
            new \Twig_SimpleFunction('can_free_activity_types', [$this, 'getCanFreeActivityTypes']),
            new \Twig_SimpleFunction('ltc_source', [$this, 'findLtcSource']),
            new \Twig_SimpleFunction('flash_player', [$this, 'flashPlayer']),
            new \Twig_SimpleFunction('doc_player', [$this, 'docPlayer']),
            new \Twig_SimpleFunction('resource_player_context', [$this, 'resourcePlayerContext']),
            new \Twig_SimpleFunction('activity_visible', [$this, 'isActivityVisible']),
        ];
    }

    public function resourcePlayerContext($file)
    {
        return $this->getResourceFacadeService()->getPlayerContext($file);
    }

    public function prepareVideoMediaUri($video)
    {
        $type = $this->getActivityService()->getActivityConfig('video');

        return $type->prepareMediaUri($video);
    }

    public function convertMinuteAndSecond($second)
    {
        $result = [];
        if (!empty($second)) {
            $result['minute'] = (int) ($second / 60);
            $result['second'] = (int) ($second % 60);
        }

        return $result;
    }

    public function flashPlayer($file, $ssl)
    {
        return $this->getPlayerService()->getFlashFilePlayer($file, $ssl);
    }

    public function docPlayer($doc, $ssl)
    {
        list($result, $error) = $this->getPlayerService()->getDocFilePlayer($doc, $ssl);

        return [
            'error' => $error,
            'result' => $result,
        ];
    }

    public function findLtcSource($courseId, $taskId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $cdnSetting = $this->getSettingService()->get('cdn');
        $cdn = '';
        if (!empty($cdnSetting) && !empty($cdnSetting['enabled'])) {
            $cdn = empty($cdnSetting['defaultUrl']) ? '' : $cdnSetting['defaultUrl'];
        }
        $task = $this->getTaskService()->getTask($taskId);
        $context = [
            'courseId' => $course['id'],
            'courseSetId' => $course['courseSetId'],
            'taskId' => empty($task) ? 0 : $task['id'],
            'activityId' => empty($task) ? 0 : $task['activityId'],
        ];

        return json_encode([
            'resource' => $this->findStaticResource(),
            'context' => $context,
            'editorConfig' => [
                'filebrowserImageUploadUrl' => $this->generateUrl('editor_upload', ['token' => $this->getWebExtension()->makeUploadToken('course')]),
                'filebrowserFlashUploadUrl' => $this->generateUrl('editor_upload', ['token' => $this->getWebExtension()->makeUploadToken('course', 'flash')]),
                'imageDownloadUrl' => $this->generateUrl('editor_download', ['token' => $this->getWebExtension()->makeUploadToken('course')]),
            ],
        ]);
    }

    protected function findStaticResource()
    {
        $cdnSetting = $this->getSettingService()->get('cdn');
        $cdn = '';
        if (!empty($cdnSetting) && !empty($cdnSetting['enabled'])) {
            $cdn = empty($cdnSetting['defaultUrl']) ? '' : $cdnSetting['defaultUrl'];
        }

        $version = $this->container->getParameter('app_version');

        $resources = [
            'jquery' => $cdn.'/static-dist/libs/jquery/dist/jquery.min.js',
            'codeage-design.css' => $cdn.'/static-dist/libs/codeages-design/dist/codeages-design.css',
            'codeage-design' => $cdn.'/static-dist/libs/codeages-design/dist/codeages-design.js',
            'validate' => $cdn.'/static-dist/libs/jquery-validation/dist/jquery.validate.js',
            'bootstrap.css' => $cdn.'/static-dist/libs/bootstrap/dist/css/bootstrap.css',
            'bootstrap' => $cdn.'/static-dist/libs/bootstrap/dist/js/bootstrap.min.js',
            'editor' => $cdn.'/static-dist/libs/es-ckeditor/ckeditor.js',
            'scrollbar' => $cdn.'/static-dist/libs/perfect-scrollbar.js',
            'es-ckeditor-highlight' => $cdn.'/static-dist/libs/es-ckeditor/plugins/codesnippet/lib/highlight/highlight.pack.js',
            'es-ckeditor-highlight-zenburn.css' => $cdn.'/static-dist/libs/es-ckeditor/plugins/codesnippet/lib/highlight/styles/zenburn.css',
        ];

        foreach ($resources as &$resource) {
            $resource = $resource.'?v='.$version;
        }

        return $resources;
    }

    public function getActivityMeta($type = null)
    {
        // todo 获取activity信息要重构
        $activities = $this->container->get('extension.manager')->getActivities();
        $customActivities = $this->container->get('activity_config_manager')->getInstalledActivities();
        foreach ($activities as &$activity) {
            $activity['meta']['name'] = $this->container->get('translator')->trans($activity['meta']['name']);
        }

        foreach ($customActivities as $customActivity) {
            if (!isset($activities[$customActivity['type']])) {
                $activities[$customActivity['type']] = [
                    'meta' => [
                        'name' => $this->container->get('translator')->trans($customActivity['name']),
                        'icon' => $customActivity['icon']['value'],
                    ],
                ];
            }
        }

        if (empty($type)) {
            $activities = array_map(function ($activity) {
                return $activity['meta'];
            }, $activities);

            return $activities;
        } else {
            if (isset($activities[$type]) && isset($activities[$type]['meta'])) {
                return $activities[$type]['meta'];
            }

            return [
                'icon' => '',
                'name' => '',
            ];
        }
    }

    /**
     * @param $type
     * @param $courseSet
     * @param $course
     *
     * @return bool
     */
    public function isActivityVisible($type, $courseSet, $course)
    {
        $activities = $this->container->get('extension.manager')->getActivities();

        return isset($activities[$type]) ? call_user_func($activities[$type]['visible'], $courseSet, $course) : false;
    }

    public function lengthFormat($len, $type = null)
    {
        if (empty($len) || 0 == $len) {
            return null;
        }

        if (in_array($type, ['testpaper', 'live'])) {
            $len *= 60;
        }
        $h = floor($len / 3600);
        $m = fmod(floor($len / 60), 60);
        $s = fmod($len, 60);

        return $h > 0 ? (($h < 10 ? '0'.$h : $h).':'.($m < 10 ? '0'.$m : $m).':'.($s < 10 ? '0'.$s : $s)) : (($m < 10 ? '0'.$m : $m).':'.($s < 10 ? '0'.$s : $s));
    }

    public function getName()
    {
        return 'web_activity_twig';
    }

    public function getCanFreeActivityTypes()
    {
        $types = [];
        $activities = $this->container->get('extension.manager')->getActivities();
        foreach ($activities as $type => $activity) {
            if (isset($activity['canFree']) && $activity['canFree']) {
                $types[$type] = $this->container->get('translator')->trans($activity['meta']['name']);
            }
        }

        return $types;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return PlayerService
     */
    protected function getPlayerService()
    {
        return $this->biz->service('Player:PlayerService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return MaterialLibService
     */
    protected function getMaterialLibService()
    {
        return $this->biz->service('MaterialLib:MaterialLibService');
    }

    protected function getWebExtension()
    {
        return $this->container->get('web.twig.extension');
    }

    protected function generateUrl($route, $parameters)
    {
        return $this->container->get('router')->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    protected function getResourceFacadeService()
    {
        return $this->biz->service('CloudPlatform:ResourceFacadeService');
    }
}
