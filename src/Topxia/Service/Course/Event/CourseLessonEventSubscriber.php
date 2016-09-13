<?php
namespace Topxia\Service\Course\Event;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseLessonEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.lesson.create'                => array('onCourseLessonCreate', 0),
            'course.lesson.delete'                => array('onCourseLessonDelete', 0),
            'course.lesson.update'                => 'onCourseLessonUpdate',
            'course.lesson.generate.replay'       => 'onCourseLessonGenerateReplay',
            'course.lesson.publish'               => 'onCourseLessonPublish',
            'course.lesson.unpublish'             => 'onCourseLessonUnpublish',
            'course.lesson_start'                 => 'onLessonStart',
            'course.lesson_finish'                => 'onLessonFinish',
            'course.material.create'              => 'onMaterialCreate',
            'course.material.update'              => 'onMaterialUpdate',
            'course.material.delete'              => 'onMaterialDelete',
            'chapter.create'                      => 'onChapterCreate',
            'chapter.delete'                      => 'onChapterDelete',
            'chapter.update'                      => 'onChapterUpdate',
            'course.lesson.generate.video.replay' => 'onLiveLessonGenerateVideo'
        );
    }

    public function onCourseLessonCreate(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $argument = $context['argument'];
        $lesson   = $context['lesson'];

        $this->createRealTimeTestCrontab($lesson);

        $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($lesson['courseId']);

        foreach ($classroomIds as $classroomId) {
            $classroom = $this->getClassroomService()->getClassroom($classroomId);
            $lessonNum = $classroom['lessonNum'] + 1;
            $this->getClassroomService()->updateClassroom($classroomId, array('lessonNum' => $lessonNum));
        }

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($lesson['courseId'], 1), 'id');

        if ($courseIds) {
            $argument['copyId'] = $lesson['id'];

            if (array_key_exists('type', $argument) && $argument['type'] == 'testpaper') {
                $lockedTarget = '';

                foreach ($courseIds as $courseId) {
                    $lockedTarget .= "'course-".$courseId."',";
                }

                $lockedTarget = "(".trim($lockedTarget, ',').")";
                $testpaperIds = ArrayToolkit::column($this->getTestpaperService()->findTestpapersByCopyIdAndLockedTarget($argument['mediaId'], $lockedTarget), 'id');
            }

            foreach ($courseIds as $key => $courseId) {
                if (array_key_exists('type', $argument) && $argument['type'] == 'testpaper') {
                    $argument['mediaId'] = $testpaperIds[$key];
                }

                $argument['courseId'] = $courseId;
                $this->getCourseService()->createLesson($argument);
            }
        }
    }

    public function onCourseLessonDelete(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $lesson   = $context["lesson"];
        $courseId = $context["courseId"];

        $this->deleteRealTimeTestCrontab($lesson);

        $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($courseId);

        foreach ($classroomIds as $key => $value) {
            $classroom = $this->getClassroomService()->getClassroom($value);
            $lessonNum = $classroom['lessonNum'] - 1;
            $this->getClassroomService()->updateClassroom($value, array("lessonNum" => $lessonNum));
        }

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId, 1), 'id');

        if ($courseIds) {
            $lesson    = $context["lesson"];
            $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($lesson['id'], $courseIds), 'id');

            foreach ($lessonIds as $key => $lessonId) {
                $this->getCourseService()->deleteLesson($courseIds[$key], $lessonId);
            }
        }
    }

    public function onCourseLessonGenerateReplay(ServiceEvent $event)
    {
        $context       = $event->getSubject();
        $courseIds     = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($context['courseId'], 1), 'id');
        $lessonReplays = $this->getCourseService()->getCourseLessonReplayByLessonId($context['lessonId']);

        if ($courseIds) {
            $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($context['lessonId'], $courseIds), 'id');

            foreach ($courseIds as $key => $courseId) {
                if ($lessonReplays) {
                    foreach ($lessonReplays as $lessonReplay) {
                        unset($lessonReplay['id']);
                        $lessonReplay['courseId']    = $courseId;
                        $lessonReplay['lessonId']    = $lessonIds[$key];
                        $lessonReplay['createdTime'] = time();
                        $this->getCourseService()->addCourseLessonReplay($lessonReplay);
                    }
                }
            }
        }
    }

    public function onCourseLessonUpdate(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $argument = $context['argument'];
        $lesson   = $context['lesson'];

        if (!empty($lesson) && $lesson['type'] == 'testpaper') {
            unset($argument['mediaId']);
        }

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($lesson['courseId'], 1), 'id');

        if ($courseIds) {
            $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($lesson['id'], $courseIds), 'id');

            foreach ($courseIds as $key => $courseId) {
                $this->getCourseService()->updateLesson($courseId, $lessonIds[$key], $argument);
            }
        }
    }

    public function onCourseLessonPublish(ServiceEvent $event)
    {
        $lesson    = $event->getSubject();
        $courseId  = $lesson["courseId"];
        $lessonId  = $lesson["id"];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId, 1), 'id');

        if ($courseIds) {
            $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($lessonId, $courseIds), 'id');

            foreach ($courseIds as $key => $courseId) {
                $this->getCourseService()->publishLesson($courseId, $lessonIds[$key]);
            }
        }
    }

    public function onCourseLessonUnpublish(ServiceEvent $event)
    {
        $lesson    = $event->getSubject();
        $courseId  = $lesson["courseId"];
        $lessonId  = $lesson["id"];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId, 1), 'id');

        if ($courseIds) {
            $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($lessonId, $courseIds), 'id');

            foreach ($courseIds as $key => $courseId) {
                $this->getCourseService()->unpublishLesson($courseId, $lessonIds[$key]);
            }
        }
    }

    public function onLessonStart(ServiceEvent $event)
    {
        $lesson  = $event->getSubject();
        $course  = $event->getArgument('course');
        $private = $course['status'] == 'published' ? 0 : 1;

        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);
            $classroom = $this->getClassroomService()->getClassroom($classroom['classroomId']);

            if (array_key_exists('showable', $classroom) && $classroom['showable'] == 1) {
                $private = 0;
            } else {
                $private = 1;
            }
        }

        $this->getStatusService()->publishStatus(array(
            'type'       => 'start_learn_lesson',
            'courseId'   => $course['id'],
            'objectType' => 'lesson',
            'objectId'   => $lesson['id'],
            'private'    => $private,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'lesson' => $this->simplifyLesson($lesson)
            )
        ));
    }

    public function onLessonFinish(ServiceEvent $event)
    {
        $lesson  = $event->getSubject();
        $course  = $event->getArgument('course');
        $private = $course['status'] == 'published' ? 0 : 1;

        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);
            $classroom = $this->getClassroomService()->getClassroom($classroom['classroomId']);

            if (array_key_exists('showable', $classroom) && $classroom['showable'] == 1) {
                $private = 0;
            } else {
                $private = 1;
            }
        }

        $user             = ServiceKernel::instance()->getCurrentUser();
        $userLessonLearns = $this->getCourseService()->searchLearns(array('userId' => $user['id'], 'lessonId' => $lesson['id'], 'status' => 'finished'), array('startTime', 'ASC'), 0, 1);

        $this->getStatusService()->publishStatus(array(
            'type'       => 'learned_lesson',
            'courseId'   => $course['id'],
            'objectType' => 'lesson',
            'objectId'   => $lesson['id'],
            'private'    => $private,
            'properties' => array(
                'course'               => $this->simplifyCousrse($course),
                'lesson'               => $this->simplifyLesson($lesson),
                'lessonLearnStartTime' => $userLessonLearns ? $userLessonLearns[0]['startTime'] : 0
            )
        ));
    }

    public function onMaterialCreate(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $argument = $context['argument'];
        $material = $context['material'];

        if ($material['lessonId'] && $material['source'] == 'coursematerial' && $material['type'] == 'course') {
            $this->getCourseService()->increaseLessonMaterialCount($material['lessonId']);
        }
    }

    public function onMaterialUpdate(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $argument = $context['argument'];
        $material = $context['material'];

        $lesson = $this->getCourseService()->getCourseLesson($material['courseId'], $material['lessonId']);

        if ($material['source'] == 'coursematerial') {
            if ($material['lessonId']) {
                $this->getCourseService()->increaseLessonMaterialCount($material['lessonId']);
            } elseif ($material['lessonId'] == 0 && isset($argument['lessonId']) && $argument['lessonId']) {
                $material['lessonId'] = $argument['lessonId'];
                $this->_waveLessonMaterialNum($material);
            }
        }
    }

    public function onMaterialDelete(ServiceEvent $event)
    {
        $material = $event->getSubject();

        $lesson = $this->getCourseService()->getCourseLesson($material['courseId'], $material['lessonId']);

        if ($lesson) {
            $this->_resetLessonMediaId($material);
            $this->_waveLessonMaterialNum($material);
        }
    }

    public function onChapterCreate(ServiceEvent $event)
    {
        $context   = $event->getSubject();
        $argument  = $context['argument'];
        $chapter   = $context['chapter'];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($chapter['courseId'], 1), 'id');

        if ($courseIds) {
            $argument['copyId'] = $chapter['id'];

            foreach ($courseIds as $courseId) {
                $argument['courseId'] = $courseId;
                $this->getCourseService()->createChapter($argument);
            }
        }
    }

    public function onChapterDelete(ServiceEvent $event)
    {
        $chapter   = $event->getSubject();
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($chapter['courseId'], 1), 'id');

        if ($courseIds) {
            $chapterIds = ArrayToolkit::column($this->getCourseService()->findChaptersByCopyIdAndLockedCourseIds($chapter['id'], $courseIds), 'id');

            foreach ($chapterIds as $key => $chapterId) {
                $this->getCourseService()->deleteChapter($courseIds[$key], $chapterId);
            }
        }
    }

    public function onChapterUpdate(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $argument = $context['argument'];

        if (isset($argument['parentId'])) {
            unset($argument['parentId']);
        }

        $chapter   = $context['chapter'];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($chapter['courseId'], 1), 'id');

        if ($courseIds) {
            $chapterIds = ArrayToolkit::column($this->getCourseService()->findChaptersByCopyIdAndLockedCourseIds($chapter['id'], $courseIds), 'id');

            foreach ($chapterIds as $key => $chapterId) {
                $argument['courseId'] = $courseIds[$key];
                $this->getCourseService()->updateChapter($courseIds[$key], $chapterId, $argument);
            }
        }
    }

    public function onLiveLessonGenerateVideo(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $lesson  = $context['lesson'];

        if (!empty($lesson) && $lesson['replayStatus'] != 'videoGenerated') {
            return false;
        }

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($lesson['courseId'], 1), 'id');

        if ($courseIds) {
            $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($lesson['id'], $courseIds), 'id');

            foreach ($courseIds as $key => $courseId) {
                $this->getCourseService()->generateLessonVideoReplay($courseId, $lessonIds[$key], $lesson['mediaId']);
            }
        }
    }

    protected function simplifyCousrse($course)
    {
        return array(
            'id'      => $course['id'],
            'title'   => $course['title'],
            'picture' => $course['middlePicture'],
            'type'    => $course['type'],
            'rating'  => $course['rating'],
            'about'   => StringToolkit::plain($course['about'], 100),
            'price'   => $course['price']
        );
    }

    protected function simplifyLesson($lesson)
    {
        return array(
            'id'      => $lesson['id'],
            'number'  => $lesson['number'],
            'type'    => $lesson['type'],
            'title'   => $lesson['title'],
            'summary' => StringToolkit::plain($lesson['summary'], 100)
        );
    }

    private function createRealTimeTestCrontab($lesson)
    {
        if (!$this->isRealTimeTest($lesson)) {
            return;
        }

        $testPaper = $this->getTestpaperService()->getTestpaper($lesson['mediaId']);
        $second    = $testPaper['limitedTime'] * 60 + 3600;

        $updateRealTimeTestResultStatusJob = array(
            'name'            => 'updateRealTimeTestResultStatus',
            'cycle'           => 'once',
            'jobClass'        => 'Topxia\\Service\\Testpaper\\Job\\UpdateRealTimeTestResultStatusJob',
            'jobParams'       => '',
            'targetType'      => "lesson",
            'targetId'        => $lesson['id'],
            'nextExcutedTime' => $lesson['testStartTime'] + $second
        );

        $this->getCrontabJobService()->createJob($updateRealTimeTestResultStatusJob);
    }

    private function deleteRealTimeTestCrontab($lesson)
    {
        $jobName = 'updateRealTimeTestResultStatus';

        $crontabJob = $this->getCrontabJobService()->findJobByNameAndTargetTypeAndTargetId($jobName, 'lesson', $lesson['id']);

        if (empty($crontabJob)) {
            return;
        }

        $this->getCrontabJobService()->deleteJob($crontabJob['id']);
    }

    private function updateRealTimeTestCrontab($lesson)
    {
        if (!$this->isRealTimeTest($lesson)) {
            $this->deleteRealTimeTestCrontab($lesson);
            return;
        }

        $jobName = 'updateRealTimeTestResultStatus';

        $crontabJob = $this->getCrontabJobService()->findJobByNameAndTargetTypeAndTargetId($jobName, 'lesson', $lesson['id']);

        if (empty($crontabJob)) {
            $this->createRealTimeTestCrontab($lesson);
            return;
        }

        $testPaper                 = $this->getTestpaperService()->getTestpaper($lesson['mediaId']);
        $fields['nextExcutedTime'] = $lesson['testStartTime'] + $testPaper['limitedTime'] * 60 + 3600;
        $this->getCrontabJobService()->updateJob($crontabJob['id'], $fields);
    }

    private function isRealTimeTest($lesson)
    {
        return $lesson['type'] == 'testpaper' && !empty($lesson['testMode']) && $lesson['testMode'] == 'realTime';
    }

    private function _resetLessonMediaId($material)
    {
        if ($material['lessonId'] && $material['source'] == 'courselesson') {
            $this->getCourseService()->resetLessonMediaId($material['lessonId']);
        }

        return false;
    }

    private function _waveLessonMaterialNum($material)
    {
        if ($material['lessonId'] && $material['source'] == 'coursematerial' && $material['type'] == 'course') {
            $count = $this->getMaterialService()->searchMaterialCount(array(
                'courseId' => $material['courseId'],
                'lessonId' => $material['lessonId'],
                'source'   => 'coursematerial',
                'type'     => 'course'
            )
            );
            $this->getCourseService()->resetLessonMaterialCount($material['lessonId'], $count);
            return true;
        }

        return false;
    }

    protected function getStatusService()
    {
        return ServiceKernel::instance()->createService('User.StatusService');
    }

    private function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getTestpaperService()
    {
        return ServiceKernel::instance()->createService('Testpaper.TestpaperService');
    }

    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileService');
    }

    protected function getCrontabJobService()
    {
        return ServiceKernel::instance()->createService('Crontab.CrontabService');
    }

    protected function getMaterialService()
    {
        return ServiceKernel::instance()->createService('Course.MaterialService');
    }
}
