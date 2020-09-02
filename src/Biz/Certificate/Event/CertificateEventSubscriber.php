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
        ];
    }

    public function onCourseTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();
        $course = $this->getCourseService()->getCourse($taskResult['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $this->processCourseCertificate($courseSet, $course, $taskResult);
        $this->processClassroomCertificate($course, $taskResult['userId']);
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

    protected function processCourseCertificate($courseSet, $course, $taskResult)
    {
        $student = $this->getCourseMemberService()->getCourseMember($taskResult['courseId'], $taskResult['userId']);

        if (empty($student['finishedTime']) || $student['learnedCompulsoryTaskNum'] != $course['compulsoryTaskNum']) {
            return;
        }
        $certificates = $this->getCertificateService()->findByTargetIdAndTargetType($course['id'], 'course');
        foreach ($certificates as $certificate) {
            $this->getRecordService()->autoIssueCertificates($certificate['id'], [$taskResult['userId']]);
        }
    }

    protected function processClassroomCertificate($course, $userId)
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
        $memberCounts = $this->getCourseMemberService()->countMembers(['finishedTime_GT' => 0, 'userId' => $userId, 'courseIds' => $courseIds]);

        //没有全部完成忽略
        if ($memberCounts < count($courseIds) || empty($memberCounts)) {
            return true;
        }

        foreach ($certificates as $certificate) {
            $this->getRecordService()->autoIssueCertificates($certificate['id'], [$userId]);
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
}
