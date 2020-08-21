<?php

namespace Biz\Certificate\Event;

use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\RecordService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CertificateEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'course.task.finish' => 'onCourseTaskFinish',
        ];
    }

    public function onCourseTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();
        $course = $this->getCourseService()->getCourse($taskResult['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $this->processCourseCertificate($courseSet, $course, $taskResult);
        $this->processClassroomCertificate($courseSet, $taskResult['userId']);
    }

    protected function processCourseCertificate($courseSet, $course, $taskResult)
    {
        $student = $this->getCourseMemberService()->getCourseMember($taskResult['courseId'], $taskResult['userId']);

        if (empty($student['finishedTime']) || $student['learnedCompulsoryTaskNum'] != $course['compulsoryTaskNum']) {
            return;
        }
        $certificates = $this->getCertificateService()->findByTargetIdAndTargetType($courseSet['id'], 'course');
        foreach ($certificates as $certificate) {
            $this->getRecordService()->autoIssueCertificates($certificate['id'], [$taskResult['userId']]);
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
}
