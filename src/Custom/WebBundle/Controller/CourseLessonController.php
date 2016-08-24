<?php 
namespace Custom\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Topxia\WebBundle\Controller\CourseLessonController as BaseCourseLessonController;

class CourseLessonController extends BaseCourseLessonController
{
    public function showAction(Request $request, $courseId, $lessonId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        $lesson         = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $json           = array();

        if ($course['studyModel'] == 'ordered') {
            $user = $this->getCurrentUser();

            $lessons = $this->getCourseService()->getCourseLessons($courseId);

            foreach ($lessons as $tempLesson) {
                if ($tempLesson['seq'] < $lesson['seq']) {
                    $lessonLearnStatus = $this->getCourseService()->getUserLearnLessonStatus($user['id'], $courseId, $tempLesson['id']);

                    if ($lessonLearnStatus == null || $lessonLearnStatus == 'learning') {
                        $json['studyModel'] = 'ordered';

                        return $this->createJsonResponse($json);
                    }
                }
            }
        }

        $json['number'] = $lesson['number'];
        $chapter        = empty($lesson['chapterId']) ? null : $this->getCourseService()->getChapter($course['id'], $lesson['chapterId']);

        if ($chapter['type'] == 'unit') {
            $unit               = $chapter;
            $json['unit']       = $unit;
            $json['unitNumber'] = $unit['number'];

            $chapter               = $this->getCourseService()->getChapter($course['id'], $unit['parentId']);
            $json['chapter']       = $chapter;
            $json['chapterNumber'] = empty($chapter) ? 0 : $chapter['number'];
        } else {
            $json['chapterNumber'] = empty($chapter) ? 0 : $chapter['number'];
            $json['unitNumber']    = 0;
        }

        $json['title']                  = $lesson['title'];
        $json['summary']                = $lesson['summary'];
        $json['type']                   = $lesson['type'];
        $json['content']                = $lesson['content'];
        $json['status']                 = $lesson['status'];
        $json['quizNum']                = $lesson['quizNum'];
        $json['materialNum']            = $lesson['materialNum'];
        $json['mediaId']                = $lesson['mediaId'];
        $json['mediaSource']            = $lesson['mediaSource'];
        $json['startTimeFormat']        = date("m-d H:i", $lesson['startTime']);
        $json['endTimeFormat']          = date("H:i", $lesson['endTime']);
        $json['startTime']              = $lesson['startTime'];
        $json['endTime']                = $lesson['endTime'];
        $json['id']                     = $lesson['id'];
        $json['courseId']               = $lesson['courseId'];
        $json['videoWatermarkEmbedded'] = 0;
        $json['liveProvider']           = $lesson["liveProvider"];
        $json['nowDate']                = time();
        $json['testMode']               = $lesson['testMode'];
        $json['testStartTime']          = $lesson['testStartTime'];
        $json['testStartTimeFormat']    = date("m-d H:i", $lesson['testStartTime']);

        if ($lesson['testMode'] == 'realTime') {
            $testpaper                 = $this->getTestpaperService()->getTestpaper($lesson['mediaId']);
            $json['limitedTime']       = $testpaper['limitedTime'];
            $minute                    = '+'.$testpaper['limitedTime'].'minute';
            $json['testEndTime']       = strtotime($minute, $lesson['testStartTime']);
            $json['testEndTimeFormat'] = date("m-d H:i", $json['testEndTime']);
        }

        $app = $this->getAppService()->findInstallApp('Homework');

        if (!empty($app)) {
            $homework                      = $this->getHomeworkService()->getHomeworkByLessonId($lesson['id']);
            $exercise                      = $this->getExerciseService()->getExerciseByLessonId($lesson['id']);
            $json['homeworkOrExerciseNum'] = $homework['itemCount'] + $exercise['itemCount'];
        } else {
            $json['homeworkOrExerciseNum'] = 0;
        }

        $json['isTeacher'] = $this->getCourseService()->isCourseTeacher($courseId, $this->getCurrentUser()->id);

        if ($lesson['type'] == 'live' && $lesson['replayStatus'] == 'generated') {
            $replaysLesson  = $this->getCourseService()->getCourseLessonReplayByLessonId($lesson['id']);
            $visableReplays = array();

            foreach ($replaysLesson as $key => $value) {
                if ($value['hidden'] == 0) {
                    $visableReplays[] = $value;
                }
            }

            $json['replays'] = $visableReplays;

            if (!empty($json['replays'])) {
                foreach ($json['replays'] as $key => $value) {
                    $url = $this->generateUrl('live_course_lesson_replay_entry', array(
                        'courseId'             => $lesson['courseId'],
                        'lessonId'             => $lesson['id'],
                        'courseLessonReplayId' => $value['id']
                    ), true);
                    $json['replays'][$key]["url"] = $url;
                }
            }
        }

        if ($json['mediaSource'] == 'self') {
            $file = $this->getUploadFileService()->getFullFile($lesson['mediaId']);

            if (!empty($file)) {
                if ($file['storage'] == 'cloud') {
                    if ($file['type'] == 'video' && $file['convertStatus'] != 'success') {
                        $json['mediaConvertStatus'] = 'doing';
                    }

                    if ($file['type'] == 'ppt') {
                        $json['mediaUri'] = $this->generateUrl('course_lesson_ppt', array(
                            'courseId' => $course['id'],
                            'lessonId' => $lesson['id']
                        ));
                    } elseif ($file['type'] == 'document') {
                        $json['mediaUri'] = $this->generateUrl('course_lesson_document', array(
                            'courseId' => $course['id'],
                            'lessonId' => $lesson['id']
                        ));
                    } elseif (!in_array($file['type'], array('video', 'audio'))) {
                        $api              = CloudAPIFactory::create("leaf");
                        $result           = $api->get("/resources/{$file['globalId']}/player");
                        $json['mediaUri'] = $result['url'];
                    }
                } else {
                    $json['mediaUri'] = $this->generateUrl('course_lesson_media', array(
                        'courseId' => $course['id'],
                        'lessonId' => $lesson['id']
                    ));
                }

                if ($this->setting('magic.lesson_watch_limit') && $course['watchLimit'] > 0) {
                    $user        = $this->getCurrentUser();
                    $watchStatus = $this->getCourseService()->checkWatchNum($user['id'], $lesson['id']);

                    if ($watchStatus['status'] == 'error') {
                        $wathcLimitTime     = $this->container->get('topxia.twig.web_extension')->durationTextFilter($watchStatus['watchLimitTime']);
                        $json['mediaError'] = "您的观看时长已到 <strong>{$wathcLimitTime}</strong>，不能再观看。";
                    }
                }
            } else {
                $json['mediaUri'] = '';

                if ($lesson['type'] == 'video') {
                    $json['mediaError'] = '抱歉，视频文件不存在，暂时无法学习。';
                } elseif ($lesson['type'] == 'audio') {
                    $json['mediaError'] = '抱歉，音频文件不存在，暂时无法学习。';
                } elseif ($lesson['type'] == 'ppt') {
                    $json['mediaError'] = '抱歉，PPT文件不存在，暂时无法学习。';
                }
            }
        } elseif ($json['mediaSource'] == 'youku' && $this->isMobileClient()) {
            $matched = preg_match('/\/sid\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);

            if ($matched) {
                $json['mediaUri']    = "http://player.youku.com/embed/{$matches[1]}";
                $json['mediaSource'] = 'iframe';
            } else {
                $json['mediaUri'] = $lesson['mediaUri'];
            }
        } elseif ($json['mediaSource'] == 'tudou') {
            $matched = preg_match('/\/v\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);

            if ($matched) {
                $json['mediaUri']    = "http://www.tudou.com/programs/view/html5embed.action?code={$matches[1]}";
                $json['mediaSource'] = 'iframe';
            } else {
                $json['mediaUri'] = $lesson['mediaUri'];
            }
        } else {
            $json['mediaUri'] = $lesson['mediaUri'];
        }

        $json['canLearn'] = $this->getCourseService()->canLearnLesson($lesson['courseId'], $lesson['id']);

        return $this->createJsonResponse($json);
    }
}
