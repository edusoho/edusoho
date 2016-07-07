<?php
namespace Topxia\Service\Course\Event;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.join'            => 'onCourseJoin',
            'course.favorite'        => 'onCourseFavorite',
            'course.note.create'     => 'onCourseNoteCreate',
            'course.note.update'     => 'onCourseNoteUpdate',
            'course.note.delete'     => 'onCourseNoteDelete',
            'course.note.liked'      => 'onCourseNoteLike',
            'course.note.cancelLike' => 'onCourseNoteCancelLike',
            'course.update'          => 'onCourseUpdate',
            'course.teacher.update'  => 'onCourseTeacherUpdate',
            'course.price.update'    => 'onCoursePriceUpdate',
            'course.picture.update'  => 'onCoursePictureUpdate',
            'announcement.create'    => 'onAnnouncementCreate',
            'announcement.update'    => 'onAnnouncementUpdate',
            'announcement.delete'    => 'onAnnouncementDelete'
        );
    }

    public function onCourseJoin(ServiceEvent $event)
    {
        $course  = $event->getSubject();
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

        $userId = $event->getArgument('userId');
        $this->getStatusService()->publishStatus(array(
            'type'       => 'become_student',
            'courseId'   => $course['id'],
            'objectType' => 'course',
            'objectId'   => $course['id'],
            'private'    => $private,
            'userId'     => $userId,
            'properties' => array(
                'course' => $this->simplifyCousrse($course)
            )
        ));
    }

    public function onCourseFavorite(ServiceEvent $event)
    {
        $course  = $event->getSubject();
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
            'type'       => 'favorite_course',
            'courseId'   => $course['id'],
            'objectType' => 'course',
            'objectId'   => $course['id'],
            'private'    => $private,
            'properties' => array(
                'course' => $this->simplifyCousrse($course)
            )
        ));
    }

    public function onCourseNoteCreate(ServiceEvent $event)
    {
        $note      = $event->getSubject();
        $classroom = $this->getClassroomService()->findClassroomByCourseId($note['courseId']);
        $course    = $this->getCourseService()->getCourse($note['courseId']);

        if ($classroom && $note['status']) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', +1);
        }

        if ($course && $note['status']) {
            $this->getCourseService()->waveCourse($note['courseId'], 'noteNum', +1);
        }
    }

    public function onCourseNoteUpdate(ServiceEvent $event)
    {
        $note      = $event->getSubject();
        $preStatus = $event->getArgument('preStatus');
        $classroom = $this->getClassroomService()->findClassroomByCourseId($note['courseId']);
        $course    = $this->getCourseService()->getCourse($note['courseId']);

        if ($classroom && $note['status'] && !$preStatus) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', +1);
        }

        if ($classroom && !$note['status'] && $preStatus) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', -1);
        }

        if ($course && $note['status'] && !$preStatus) {
            $this->getCourseService()->waveCourse($note['courseId'], 'noteNum', +1);
        }

        if ($course && !$note['status'] && $preStatus) {
            $this->getCourseService()->waveCourse($note['courseId'], 'noteNum', -1);
        }
    }

    public function onCourseNoteDelete(ServiceEvent $event)
    {
        $note      = $event->getSubject();
        $classroom = $this->getClassroomService()->findClassroomByCourseId($note['courseId']);

        if ($classroom) {
            $this->getClassroomService()->waveClassroom($classroom['classroomId'], 'noteNum', -1);
        }

        $course = $this->getCourseService()->getCourse($note['courseId']);

        if ($course) {
            $this->getCourseService()->waveCourse($note['courseId'], 'noteNum', -1);
        }
    }

    public function onCourseNoteLike(ServiceEvent $event)
    {
        $note = $event->getSubject();
        $this->getNoteService()->count($note['id'], 'likeNum', +1);
    }

    public function onCourseNoteCancelLike(ServiceEvent $event)
    {
        $note = $event->getSubject();
        $this->getNoteService()->count($note['id'], 'likeNum', -1);
    }

    public function onCourseTeacherUpdate(ServiceEvent $event)
    {
        $context = $event->getSubject();

        $courseId = $context["courseId"];

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId, 1), 'id');

        $findClassroomsByCourseIds = $this->getClassroomService()->findClassroomIdsByCourseId($courseId);

        foreach ($findClassroomsByCourseIds as $findClassroomsByCourseId) {
            $this->getClassroomService()->updateClassroomTeachers($findClassroomsByCourseId);
        }

        if ($courseIds) {
            $course = $context['course'];

            $teachers = $context['teachers'];

            foreach ($courseIds as $courseId) {
                $this->getCourseService()->setCourseTeachers($courseId, $teachers);
            }
        }
    }

    public function onCourseUpdate(ServiceEvent $event)
    {
        $context = $event->getSubject();

        $argument  = $context['argument'];
        $course    = $context['course'];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($course['id'], 1), 'id');

        if ($courseIds && $argument) {
            foreach ($courseIds as $key => $courseId) {
                $this->getCourseService()->updateCourse($courseIds[$key], $argument);
            }
        }
    }

    public function onCoursePriceUpdate(ServiceEvent $event)
    {
        $context   = $event->getSubject();
        $currency  = $context['currency'];
        $course    = $context['course'];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($course['id'], 1), 'id');

        if ($courseIds) {
            foreach ($courseIds as $courseId) {
                $this->getCourseService()->setCoursePrice($courseId, $currency, $course['price']);
            }
        }
    }

    public function onCoursePictureUpdate(ServiceEvent $event)
    {
        $context   = $event->getSubject();
        $argument  = $context['argument'];
        $course    = $context['course'];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($course['id'], 1), 'id');

        if ($courseIds) {
            foreach ($courseIds as $courseId) {
                $this->getCourseService()->changeCoursePicture($courseId, $argument);
            }
        }
    }

    public function onAnnouncementCreate(ServiceEvent $event)
    {
        $announcement = $event->getSubject();

        if ($announcement['targetType'] != 'course') {
            return false;
        }

        $course = $this->getCourseService()->getCourse($announcement['targetId']);
        if ($course['parentId'] != 0) {
            return false;
        }

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($course['id'], 1), 'id');

        if ($courseIds) {
            $fields           = ArrayToolkit::parts($announcement, array('userId', 'targetType', 'url', 'startTime', 'endTime', 'content'));
            $fields['copyId'] = $announcement['id'];

            foreach ($courseIds as $courseId) {
                $fields['targetId']    = $courseId;
                $fields['createdTime'] = time();

                $this->getAnnouncementService()->createAnnouncement($fields);
            }
        }

        return true;
    }

    public function onAnnouncementUpdate(ServiceEvent $event)
    {
        $announcement = $event->getSubject();

        if ($announcement['targetType'] != 'course') {
            return false;
        }

        $course = $this->getCourseService()->getCourse($announcement['targetId']);
        if ($course['parentId'] != 0) {
            return false;
        }

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($course['id'], 1), 'id');

        if ($courseIds) {
            $copyAnnouncements = $this->getAnnouncementService()->searchAnnouncements(
                array(
                    'targetType' => 'course',
                    'targetIds'  => $courseIds,
                    'copyId'     => $announcement['id']
                ),
                array('createdTime', 'DESC'),
                0, PHP_INT_MAX
            );

            $fields = ArrayToolkit::parts($announcement, array('url', 'startTime', 'endTime', 'content'));

            foreach ($copyAnnouncements as $copyAnnouncement) {
                $fields['updatedTime'] = time();

                $this->getAnnouncementService()->updateAnnouncement($copyAnnouncement['id'], $fields);
            }
        }

        return true;
    }

    public function onAnnouncementDelete(ServiceEvent $event)
    {
        $announcement = $event->getSubject();

        if ($announcement['targetType'] != 'course') {
            return false;
        }

        $course = $this->getCourseService()->getCourse($announcement['targetId']);
        if ($course['parentId'] != 0) {
            return false;
        }

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($course['id'], 1), 'id');

        if ($courseIds) {
            $copyAnnouncements = $this->getAnnouncementService()->searchAnnouncements(
                array(
                    'targetType' => 'course',
                    'targetIds'  => $courseIds,
                    'copyId'     => $announcement['id']
                ),
                array('createdTime', 'DESC'),
                0, PHP_INT_MAX
            );

            foreach ($copyAnnouncements as $copyAnnouncement) {
                $this->getAnnouncementService()->deleteAnnouncement($copyAnnouncement['id']);
            }
        }

        return true;
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

    protected function getStatusService()
    {
        return ServiceKernel::instance()->createService('User.StatusService');
    }

    protected function getNoteService()
    {
        return ServiceKernel::instance()->createService('Course.NoteService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileService');
    }

    protected function getAnnouncementService()
    {
        return ServiceKernel::instance()->createService('Announcement.AnnouncementService');
    }
}
