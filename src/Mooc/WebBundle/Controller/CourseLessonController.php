<?php

namespace Mooc\WebBundle\Controller;

use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\CourseLessonController as BaseCourseLessonController;

class CourseLessonController extends BaseCourseLessonController
{
    public function doTestpaperAction(Request $request, $lessonId, $testId)
    {
        $status  = 'do';
        $message = $this->checkTestPaper($lessonId, $testId, $status);

        if (!empty($message)) {
            return $this->createMessageResponse('info', $message);
        }

        return $this->forward('TopxiaWebBundle:Testpaper:doTestpaper', array('targetType' => 'lesson', 'targetId' => $lessonId, 'testId' => $testId));
    }

    public function reDoTestpaperAction(Request $request, $lessonId, $testId)
    {
        $status  = 'redo';
        $message = $this->checkTestPaper($lessonId, $testId, $status);

        if (!empty($message)) {
            return $this->createMessageResponse('info', $message);
        }

        return $this->forward('TopxiaWebBundle:Testpaper:reDoTestpaper', array('targetType' => 'lesson', 'targetId' => $lessonId, 'testId' => $testId));
    }

    private function checkTestPaper($lessonId, $testId, $status)
    {
        $user = $this->getCurrentUser();

        $message   = '';
        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        $targets = $this->get('topxia.target_helper')->getTargets(array($testpaper['target']));

        if ($targets[$testpaper['target']]['type'] != 'course') {
            throw $this->createAccessDeniedException('试卷只能属于课程');
        }

        $courseId = $targets[$testpaper['target']]['id'];

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            return $message = '试卷所属课程不存在！';
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            return $message = '不是试卷所属课程老师或学生';
        }

        if ($course['type'] == 'periodic' && time() > $course['endTime']) {
            return $message = '周期课程已经结束, 不可考试';
        }

        $lesson = $this->getCourseService()->getLesson($lessonId);

        if ($lesson['testMode'] == 'realTime') {
            $testpaper = $this->getTestpaperService()->getTestpaper($testId);

            $testEndTime = $lesson['testStartTime'] + $testpaper['limitedTime'] * 60;

            if ($testEndTime < time()) {
                return $message = '实时考试已经结束!';
            }

            if ($status == 'do') {
                $testpaperResult = $this->getTestpaperService()->findTestpaperResultsByTestIdAndStatusAndUserId($testpaper['id'], $user['id'], array('finished'));

                if ($testpaperResult) {
                    return $message = '您已经提交试卷，不能继续考试!';
                }
            } else {
                return $message = '实时考试，不能再考一次!';
            }
        }
    }

    public function showAction(Request $request, $courseId, $lessonId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        $lesson  = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $json    = array();
        $preview = $request->query->get('preview');

        if ($member['role'] != 'teacher' && $preview == 1) {
            $user = $this->getCurrentUser();

            if (!$this->getUserService()->hasAdminRoles($user['id'])) {
                return $this->createJsonResponse(array('message' => '您不是教师，无法预览!'));
            }
        }

        if ($preview != 1) {
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
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);

            if (!empty($file)) {
                if ($file['storage'] == 'cloud') {
                    $factory = new CloudClientFactory();
                    $client  = $factory->createClient();

                    $json['mediaConvertStatus'] = $file['convertStatus'];

                    if (!empty($file['convertParams']['hasVideoWatermark'])) {
                        $json['videoWatermarkEmbedded'] = 1;
                    }

                    if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
                        if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                            $token = $this->getTokenService()->makeToken('hls.playlist', array(
                                'data'     => $file['id'],
                                'times'    => $this->agentInWhiteList($request->headers->get("user-agent")) ? 0 : 3,
                                'duration' => 3600,
                                'userId'   => $this->getCurrentUser()->getId()
                            ));

                            $url = array(
                                'url' => $this->generateUrl('hls_playlist', array(
                                    'id'    => $file['id'],
                                    'token' => $token['token'],
                                    'line'  => $request->query->get('line')
                                ), true)
                            );
                        } else {
                            $url = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
                        }

                        $json['mediaHLSUri'] = $url['url'];

                        if ($this->setting('magic.lesson_watch_limit') && $course['watchLimit'] > 0) {
                            $user        = $this->getCurrentUser();
                            $watchStatus = $this->getCourseService()->checkWatchNum($user['id'], $lesson['id']);

                            if ($watchStatus['status'] == 'error') {
                                $wathcLimitTime     = $this->container->get('topxia.twig.web_extension')->durationTextFilter($watchStatus['watchLimitTime']);
                                $json['mediaError'] = "您的观看时长已到 <strong>{$wathcLimitTime}</strong>，不能再观看。";
                            }
                        }
                    } elseif ($file['type'] == 'ppt') {
                        $json['mediaUri'] = $this->generateUrl('course_lesson_ppt', array('courseId' => $course['id'], 'lessonId' => $lesson['id']));
                    } else {
                        if (!empty($file['metas']) && !empty($file['metas']['hd']['key'])) {
                            $key = $file['metas']['hd']['key'];
                        } else {
                            if ($file['type'] == 'video') {
                                $key = null;
                            } else {
                                $key = $file['hashId'];
                            }
                        }

                        if ($key) {
                            $url              = $client->generateFileUrl($client->getBucket(), $key, 3600);
                            $json['mediaUri'] = $url['url'];
                        } else {
                            $json['mediaUri'] = '';
                        }
                    }
                } else {
                    $json['mediaUri'] = $this->generateUrl('course_lesson_media', array('courseId' => $course['id'], 'lessonId' => $lesson['id']));

                    if ($this->setting('magic.lesson_watch_limit') && $course['watchLimit'] > 0) {
                        $user        = $this->getCurrentUser();
                        $watchStatus = $this->getCourseService()->checkWatchNum($user['id'], $lesson['id']);

                        if ($watchStatus['status'] == 'error') {
                            $wathcLimitTime     = $this->container->get('topxia.twig.web_extension')->durationTextFilter($watchStatus['watchLimitTime']);
                            $json['mediaError'] = "您的观看时长已到 <strong>{$wathcLimitTime}</strong>，不能再观看。";
                        }
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
        } elseif ($json['mediaSource'] == 'youku' && $this->isMobile()) {
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
