<?php

namespace Biz\Certificate\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\RecordService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CertificateEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'course.task.finish' => 'onCourseTaskFinish',
            'certificate.publish' => 'onCertificatePublish',
            'classroom.course.delete' => 'onClassroomCourseDelete',
            'course.task.delete' => 'onCourseTaskDelete',
            'course.lesson.setOptional' => 'onLessonSetOptional',
            'course.task.update.sync' => 'onCourseTaskUpdateSync',
        ];
    }

    public function onClassroomCourseDelete(Event $event)
    {
        $classroom = $event->getSubject();
        $certificates = $this->getCertificateService()->findByTargetIdAndTargetType($classroom['id'], 'classroom');
        foreach ($certificates as $certificate) {
            $this->getSchedulerService()->register([
                'name' => 'issue_certificate_job'.$certificate['id'],
                'pool' => 'dedicated',
                'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                'expression' => (int) time(),
                'misfire_policy' => 'executing',
                'class' => 'Biz\Certificate\Job\IssueCertificateJob',
                'args' => ['certificateId' => $certificate['id']],
            ]);
        }
    }

    public function onCourseTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();
        $course = $this->getCourseService()->getCourse($taskResult['courseId']);

        $this->processCourseCertificate($course, [$taskResult['userId']]);
        $this->processClassroomCertificate($course, [$taskResult['userId']]);
    }

    public function onLessonSetOptional(Event $event)
    {
        $lesson = $event->getSubject();
        $course = $this->getCourseService()->getCourse($lesson['courseId']);
        $students = $this->getCourseMemberService()->searchMembers(
            ['courseId' => $course['id'], 'role' => 'student'],
            ['createdTime' => 'DESC'],
            0,
            PHP_INT_MAX
        );
        $userIds = ArrayToolkit::column($students, 'userId');

        $this->processCourseCertificate($course, $userIds);
        $this->processClassroomCertificate($course, $userIds);
    }

    public function onCourseTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        $course = $this->getCourseService()->getCourse($task['courseId']);

        $certificates = [];
        if (empty($course['parentId'])) {
            $certificates = $this->getCertificateService()->findByTargetIdAndTargetType($task['courseId'], 'course');
        } else {
            $classroomIds = ArrayToolkit::column($this->getClassroomService()->findClassroomIdsByCourseId($course['id']), 'classroomId');
            if (empty($classroomIds)) {
                return true;
            }
            $certificates = $this->getCertificateService()->findByTargetIdAndTargetType($classroomIds[0], 'classroom');
        }

        foreach ($certificates as $certificate) {
            $this->getSchedulerService()->register([
                'name' => 'issue_certificate_job'.$certificate['id'],
                'pool' => 'dedicated',
                'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                'expression' => (int) time(),
                'misfire_policy' => 'executing',
                'class' => 'Biz\Certificate\Job\IssueCertificateJob',
                'args' => ['certificateId' => $certificate['id']],
            ]);
        }
    }

    public function onCourseTaskUpdateSync(Event $event)
    {
        $task = $event->getSubject();
        $courses = $this->getCourseService()->findCoursesByParentIdAndLocked($task['courseId'], 1);

        $certificates = [];
        foreach ($courses as $course) {
            if (empty($course['parentId'])) {
                $courseCertificates = $this->getCertificateService()->findByTargetIdAndTargetType($course['id'], 'course');
                $certificates = array_merge($certificates, $courseCertificates);
            } else {
                $classroomIds = ArrayToolkit::column($this->getClassroomService()->findClassroomIdsByCourseId($course['id']), 'classroomId');
                if (empty($classroomIds)) {
                    return true;
                }
                $classroomCertificates = $this->getCertificateService()->findByTargetIdAndTargetType($classroomIds[0], 'classroom');
                $certificates = array_merge($certificates, $classroomCertificates);
            }
        }

        $certificates = $this->getCertificateService()->findByIds(ArrayToolkit::column($certificates, 'id'));

        foreach ($certificates as $certificate) {
            $this->getSchedulerService()->register([
                'name' => 'issue_certificate_job'.$certificate['id'],
                'pool' => 'dedicated',
                'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                'expression' => (int) time(),
                'misfire_policy' => 'executing',
                'class' => 'Biz\Certificate\Job\IssueCertificateJob',
                'args' => ['certificateId' => $certificate['id']],
            ]);
        }
    }

    public function onCertificatePublish(Event $event)
    {
        $certificate = $event->getSubject();
        if ('published' != $certificate['status']) {
            return;
        }

        $this->getSchedulerService()->register([
            'name' => 'issue_certificate_job'.$certificate['id'],
            'pool' => 'dedicated',
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => intval(time()),
            'misfire_policy' => 'executing',
            'class' => 'Biz\Certificate\Job\IssueCertificateJob',
            'args' => ['certificateId' => $certificate['id']],
        ]);
    }

    protected function processCourseCertificate($course, $userIds)
    {
        $students = $this->getCourseMemberService()->searchMembers(
            ['courseId' => $course['id'], 'userIds' => $userIds],
            [],
            0,
            PHP_INT_MAX
        );
        foreach ($students as $key => $student) {
            if (empty($student['finishedTime']) || $student['learnedCompulsoryTaskNum'] != $course['compulsoryTaskNum']) {
                unset($students[$key]);
            }
        }

        $userIds = ArrayToolkit::column($students, 'userId');

        $certificates = $this->getCertificateService()->findByTargetIdAndTargetType($course['id'], 'course');
        foreach ($certificates as $certificate) {
            $this->getRecordService()->autoIssueCertificates($certificate['id'], $userIds);
        }
    }

    protected function processClassroomCertificate($course, $userIds)
    {
        $classroomCourse = $this->getClassroomService()->findClassroomIdsByCourseId($course['id']);
        $classroomIds = ArrayToolkit::column($classroomCourse, 'classroomId');
        if (empty($classroomIds)) {
            return true;
        }

        $classroomId = $classroomIds[0];
        $courses = $this->getClassroomService()->findCoursesByClassroomId($classroomId);
        $certificates = $this->getCertificateService()->findByTargetIdAndTargetType($classroomId, 'classroom');
        if (empty($certificates)) {
            return true;
        }

        $courseIds = ArrayToolkit::column($courses, 'id');
        foreach ($userIds as $key => $userId) {
            $memberCounts = $this->getCourseMemberService()->countMembers(['finishedTime_GT' => 0, 'userId' => $userId, 'courseIds' => $courseIds]);

            //没有全部完成忽略
            if ($memberCounts < count($courseIds) || empty($memberCounts)) {
                unset($userIds[$key]);
            }
        }

        foreach ($certificates as $certificate) {
            $this->getRecordService()->autoIssueCertificates($certificate['id'], $userIds);
        }
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    public function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return CertificateService
     */
    protected function getCertificateService()
    {
        return $this->getBiz()->service('Certificate:CertificateService');
    }

    /**
     * @return RecordService
     */
    protected function getRecordService()
    {
        return $this->getBiz()->service('Certificate:RecordService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
