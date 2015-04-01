<?php
namespace Topxia\Service\User\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\WebBundle\Util\TargetHelper;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class StatusEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'status.lesson_start' => 'onLessonStart',
            'status.lesson_finish' => 'onLessonFinish',
            'status.testpaper_finish' => 'onTestpaperFinish',
            'status.homework_finish' => 'onHomeworkFinish',
            'status.exercise_finish' => 'onExerciseFinish',
            'status.course_join' => 'onCourseJoin',
            'status.course_favorite' => 'onCourseFavorite',
            'status.classroom_join' => 'onClassroomJoin',
            'status.classroom_guest' => 'onClassroomGuest',
        );
    }

    public function onLessonStart(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $course = $event->getArgument('course');
        $this->getStatusService()->publishStatus(array(
            'type' => 'start_learn_lesson',
            'objectType' => 'lesson',
            'objectId' => $lesson['id'],
            'isHidden' => $course['status'] == 'published' ? 0 : 1,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'lesson' => $this->simplifyLesson($lesson),
            )
        ));
    }

    public function onLessonFinish(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $course = $event->getArgument('course');
        $this->getStatusService()->publishStatus(array(
            'type' => 'learned_lesson',
            'objectType' => 'lesson',
            'objectId' => $lesson['id'],
            'isHidden' => $course['status'] == 'published' ? 0 : 1,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'lesson' => $this->simplifyLesson($lesson),
            )
        ));
    }

    public function onTestpaperFinish(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $testpaperResult = $event->getArgument('testpaperResult');
        //TODO need to use targetHelper class for consistency
        $target = explode('-', $testpaper['target']);
        $course = $this->getCourseService()->getCourse($target[1]);
        $this->getStatusService()->publishStatus(array(
            'type' => 'finished_testpaper',
            'objectType' => 'testpaper',
            'objectId' => $testpaper['id'],
            'isHidden' => $course['status'] == 'published' ? 0 : 1,
            'properties' => array(
                'testpaper' => $this->simplifyTestpaper($testpaper),
                'result' => $this->simplifyTestpaperResult($testpaperResult),
            )
        ));
    }

    public function onHomeworkFinish(ServiceEvent $event)
    {
        $homework = $event->getSubject();
        $course = $event->getArgument('course');
        $lesson = $event->getArgument('lesson');
        $this->getStatusService()->publishStatus(array(
            'type' => 'finished_homework',
            'objectType' => 'homework',
            'objectId' => $homework['id'],
            'isHidden' => $course['status'] == 'published' ? 0 : 1,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'lesson' => $this->simplifyLesson($lesson),
                'homework' => $homework,
            )
        ));
    }

    public function onExerciseFinish(ServiceEvent $event)
    {
        $exercise = $event->getSubject();
        $course = $event->getArgument('course');
        $lesson = $event->getArgument('lesson');
        $this->getStatusService()->publishStatus(array(
            'type' => 'finished_exercise',
            'objectType' => 'exercise',
            'objectId' => $exercise['id'],
            'isHidden' => $course['status'] == 'published' ? 0 : 1,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'lesson' => $this->simplifyLesson($lesson),
                'exercise' => $exercise,
            )
        ));
    }

    public function onCourseFavorite(ServiceEvent $event)
    {
        $course = $event->getSubject();
        $this->getStatusService()->publishStatus(array(
            'type' => 'favorite_course',
            'objectType' => 'course',
            'objectId' => $course['id'],
            'isHidden' => $course['status'] == 'published' ? 0 : 1,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
            )
        ));
    }

    public function onCourseJoin(ServiceEvent $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $this->getStatusService()->publishStatus(array(
            'type' => 'become_student',
            'objectType' => 'course',
            'objectId' => $course['id'],
            'isHidden' => $course['status'] == 'published' ? 0 : 1,
            'userId' => $userId,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
            )
        ));
    }

    public function onClassroomJoin(ServiceEvent $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');
        $this->getStatusService()->publishStatus(array(
            'type' => 'become_student',
            'objectType' => 'classroom',
            'objectId' => $classroom['id'],
            'isHidden' => $classroom['status'] == 'published' ? 0 : 1,
            'userId' => $userId,
            'properties' => array(
                'classroom' => $this->simplifyClassroom($classroom),
            )
        ));
    }

    public function onClassroomGuest(ServiceEvent $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');
        $this->getStatusService()->publishStatus(array(
            'type' => 'become_auditor',
            'objectType' => 'classroom',
            'objectId' => $classroom['id'],
            'isHidden' => $classroom['status'] == 'published' ? 0 : 1,
            'userId' => $userId,
            'properties' => array(
                'classroom' => $this->simplifyClassroom($classroom),
            )
        ));
    }

    private function simplifyCousrse($course)
    {
        return array(
            'id' => $course['id'],
            'title' => $course['title'],
            'picture' => $course['middlePicture'],
            'type' => $course['type'],
            'rating' => $course['rating'],
            'about' => StringToolkit::plain($course['about'], 100),
            'price' => $course['price'],
        );
    }

    private function simplifyLesson($lesson)
    {
        return array(
            'id' => $lesson['id'],
            'number' => $lesson['number'],
            'type' => $lesson['type'],
            'title' => $lesson['title'],
            'summary' => StringToolkit::plain($lesson['summary'], 100),
        );
    }

    private function simplifyTestpaper($testpaper)
    {
        return array(
            'id' => $testpaper['id'],
            'name' => $testpaper['name'],
            'description' => StringToolkit::plain($testpaper['description'], 100),
            'score' => $testpaper['score'],
            'passedScore' => $testpaper['passedScore'],
            'itemCount' => $testpaper['itemCount'],
        );
    }

    private function simplifyTestpaperResult($testpaperResult)
    {
        return array(
            'id' => $testpaperResult['id'],
            'score' => $testpaperResult['score'],
            'objectiveScore' => $testpaperResult['objectiveScore'],
            'subjectiveScore' => $testpaperResult['subjectiveScore'],
            'teacherSay' => StringToolkit::plain($testpaperResult['teacherSay'], 100),
            'passedStatus' => $testpaperResult['passedStatus'],
        );
    }

    private function simplifyClassroom($classroom)
    {
        return array(
            'id' => $classroom['id'],
            'title' => $classroom['title'],
            'picture' => $classroom['middlePicture'],
            'about' => StringToolkit::plain($classroom['about'], 100),
            'price' => $classroom['price'],
        );
    }

    private function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    private function getStatusService()
    {
        return ServiceKernel::instance()->createService('User.StatusService');
    }
}
