<?php

namespace AppBundle\Controller\Activity;


use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\File\Service\UploadFileService;
use Biz\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\Request;

class VideoController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id, $fetchMedia = true);

        return $this->render('activity/video/show.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId
        ));
    }

    public function previewAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId'], $fetchMedia = true);
        $course   = $this->getCourseService()->getCourse($task['courseId']);
        $user     = $this->getCurrentUser();


        $tryLookTime               = 0;
        $hasVideoWatermarkEmbedded = 0;

        if ($task['type'] == 'video' && $task['mediaSource'] == 'self') {
            $file = $this->getUploadFileService()->getFullFile($task['mediaId']);

            if (empty($task['isFree']) && !empty($course['tryLookable'])) {
                $tryLookTime = empty($course['tryLookTime']) ? 0 : $course['tryLookTime'];
            }

            if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
                $factory = new CloudClientFactory();
                $client  = $factory->createClient();

                if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                    $token = $this->getTokenService()->makeToken('hls.playlist', array(
                        'data'     => array(
                            'id'          => $file['id'],
                            'tryLookTime' => $tryLookTime
                        ),
                        'times'    => $this->agentInWhiteList($request->headers->get("user-agent")) ? 0 : 3,
                        'duration' => 3600
                    ));

                    $hls = array(
                        'url' => $this->generateUrl('hls_playlist', array(
                            'id'    => $file['id'],
                            'token' => $token['token'],
                            'line'  => $request->query->get('line')
                        ), true)
                    );
                } else {
                    $hls = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
                }
            }

            if (!empty($file['convertParams']['hasVideoWatermark'])) {
                $hasVideoWatermarkEmbedded = 1;
            }
        } elseif ($task['mediaSource'] == 'youku') {
            $matched = preg_match('/\/sid\/(.*?)\/v\.swf/s', $activity['ext']['mediaUri'], $matches);

            if ($matched) {
                $task['mediaUri']    = "http://player.youku.com/embed/{$matches[1]}";
                $task['mediaSource'] = 'iframe';
            }
        } elseif ($task['mediaSource'] == 'tudou') {
            $matched = preg_match('/\/v\/(.*?)\/v\.swf/s', $activity['ext']['mediaUri'], $matches);

            if ($matched) {
                $task['mediaUri']    = "http://www.tudou.com/programs/view/html5embed.action?code={$matches[1]}";
                $task['mediaSource'] = 'iframe';
            }
        }


        return $this->render('activity/video/preview.html.twig', array(
            'activity'                  => $activity,
            'course'                    => $course,
            'task'                      => $task,
            'user'                      => $user,
            'hasVideoWatermarkEmbedded' => $hasVideoWatermarkEmbedded,
            'hlsUrl'                    => (isset($hls) && is_array($hls) && !empty($hls['url'])) ? $hls['url'] : '',
        ));
    }


    /**
     * 获取当前视频活动的文件来源
     * @param $activity
     * @return mediaSource
     */
    protected function getMediaSource($activity)
    {
        return $activity['ext']['mediaSource'];
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id, $fetchMedia = true);
        $activity = $this->fillMinuteAndSecond($activity);
        return $this->render('activity/video/modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/video/modal.html.twig', array(
            'courseId' => $courseId
        ));
    }

    protected function fillMinuteAndSecond($activity)
    {
        if (!empty($activity['length'])) {
            $activity['minute'] = intval($activity['length'] / 60);
            $activity['second'] = intval($activity['length'] % 60);
        }
        return $activity;
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

}