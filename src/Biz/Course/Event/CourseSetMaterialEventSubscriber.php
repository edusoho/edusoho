<?php

namespace Biz\Course\Event;

use Biz\Taxonomy\TagOwnerManager;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MaterialService;
use Biz\File\Service\UploadFileService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseSetMaterialEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course-set.delete' => 'onCourseSetDelete',
            'course.delete' => 'onCourseDelete',
            'upload.file.delete' => 'onUploadFileDelete',
            'upload.file.finish' => 'onUploadFileFinish',
            'upload.file.add' => 'onUploadFileFinish',
            'open.course.delete' => 'onOpenCourseDelete',
            'open.course.lesson.create' => 'onOpenCourseLessonCreate',
            'open.course.lesson.update' => 'onOpenCourseLessonUpdate',
            'open.course.lesson.delete' => 'onOpenCourseLessonDelete',
            'open.course.lesson.generate.video.replay' => 'onLiveOpenFileReplay',
        );
    }

    public function onCourseSetDelete(Event $event)
    {
        $courseSet = $event->getSubject();
        $this->getMaterialService()->deleteMaterialsByCourseSetId($courseSet['id']);
    }

    public function onCourseDelete(Event $event)
    {
        $course = $event->getSubject();
        $this->getMaterialService()->deleteMaterialsByCourseId($course['id']);

        $tagOwnerManager = new TagOwnerManager('course', $course['id']);
        $tagOwnerManager->delete();
    }

    public function onUploadFileDelete(Event $event)
    {
        $file = $event->getSubject();

        $materials = $this->getMaterialService()->searchMaterials(
            array('fileId' => $file['id'], 'copyId' => 0),
            array('createdTime' => 'DESC'), 0, PHP_INT_MAX
        );

        if (!$materials) {
            return false;
        }

        foreach ($materials as $key => $material) {
            if ('coursematerial' == $material['source'] && $material['lessonId']) {
                $this->getMaterialService()->deleteMaterial($material['courseId'], $material['id']);
            }
        }
    }

    public function onUploadFileFinish(Event $event)
    {
        $context = $event->getSubject();
        $file = $context['file'];

        if (in_array($file['targetType'], array('courseactivity', 'courselesson', 'coursematerial', 'opencourselesson', 'opencoursematerial'))) {
            if (in_array($file['targetType'], array('opencourselesson', 'opencoursematerial'))) {
                $file['courseSetId'] = 0;
                $file['courseId'] = $file['targetId'];
                $file['type'] = 'openCourse';
            } else {
                $file['courseSetId'] = $file['targetId'];
                $file['courseId'] = 0;
                $file['type'] = 'course';
            }

            $file['fileId'] = $file['id'];
            $file['source'] = $file['targetType'];
            $this->getMaterialService()->uploadMaterial($file);
        }
    }

    public function onOpenCourseDelete(Event $event)
    {
        $course = $event->getSubject();

        $this->getMaterialService()->deleteMaterialsByCourseId($course['id'], 'openCourse');
    }

    public function onOpenCourseLessonCreate(Event $event)
    {
        $context = $event->getSubject();
        $lesson = $context['lesson'];

        if (in_array($lesson['type'], array('liveOpen')) || !$lesson['mediaId']) {
            return false;
        }

        $material = $this->getMaterialService()->searchMaterials(
            array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $lesson['id'],
                'fileId' => $lesson['mediaId'],
                'source' => 'opencourselesson',
                'type' => 'openCourse',
                'courseSetId' => 0,
            ),
            array('createdTime' => 'DESC'), 0, 1
        );

        if (!$material) {
            $fields = array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $lesson['id'],
                'fileId' => $lesson['mediaId'],
                'source' => 'opencourselesson',
                'type' => 'openCourse',
                'courseSetId' => 0,
            );
            $this->getMaterialService()->uploadMaterial($fields);
        }
    }

    public function onOpenCourseLessonUpdate(Event $event)
    {
        $context = $event->getSubject();
        $lesson = $context['lesson'];
        $sourceLesson = $context['sourceLesson'];

        if (in_array($lesson['type'], array('liveOpen')) || ($lesson['mediaId'] == $sourceLesson['mediaId'])) {
            return false;
        }

        $material = $this->getMaterialService()->searchMaterials(
            array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $lesson['id'],
                'source' => 'opencourselesson',
                'type' => 'openCourse',
                'courseSetId' => 0,
            ),
            array('createdTime' => 'DESC'), 0, 1
        );

        if ($material) {
            if (0 != $lesson['mediaId'] && 'self' == $lesson['mediaSource']) {
                $this->_resetExistMaterialLessonId($material[0]);

                $fields = array(
                    'courseId' => $lesson['courseId'],
                    'lessonId' => $lesson['id'],
                    'fileId' => $lesson['mediaId'],
                    'source' => 'opencourselesson',
                    'type' => 'openCourse',
                    'courseSetId' => 0,
                );
                $this->getMaterialService()->uploadMaterial($fields);
            } elseif ('self' != $lesson['mediaSource'] && 0 == $lesson['mediaId']) {
                $this->_resetExistMaterialLessonId($material[0]);
            }
        } else {
            $fields = array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $lesson['id'],
                'fileId' => $lesson['mediaId'],
                'source' => 'opencourselesson',
                'type' => 'openCourse',
                'courseSetId' => 0,
            );
            $this->getMaterialService()->uploadMaterial($fields);
        }
    }

    public function onOpenCourseLessonDelete(Event $event)
    {
        $context = $event->getSubject();
        $lesson = $context['lesson'];

        $materials = $this->getMaterialService()->searchMaterials(
            array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $lesson['id'],
                'type' => 'openCourse',
            ),
            array('createdTime' => 'DESC'), 0, PHP_INT_MAX
        );
        if (!$materials) {
            return false;
        }

        foreach ($materials as $key => $material) {
            if (0 == $material['fileId'] && !empty($material['link'])) {
                $this->getMaterialService()->deleteMaterial($material['courseId'], $material['id']);
            } else {
                $updateFields = array(
                    'lessonId' => 0,
                );
                $this->getMaterialService()->updateMaterial($material['id'], $updateFields, array('fileId' => $material['fileId']));
            }
        }
    }

    public function onLiveFileReplay(Event $event)
    {
        $context = $event->getSubject();
        $lesson = $context['lesson'];

        if ('live' != $lesson['type'] || ('live' == $lesson['type'] && 'videoGenerated' != $lesson['replayStatus'])) {
            return false;
        }

        $material = $this->getMaterialService()->searchMaterials(
            array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $lesson['id'],
                'source' => 'courselesson',
            ),
            array('createdTime' => 'DESC'), 0, 1
        );

        if ($material) {
            $this->_resetExistMaterialLessonId($material[0]);
        }

        $fields = array(
            'courseId' => $lesson['courseId'],
            'lessonId' => $lesson['id'],
            'fileId' => $lesson['mediaId'],
            'source' => 'courselesson',
            'type' => 'course',
        );
        $this->getMaterialService()->uploadMaterial($fields);
    }

    public function onLiveOpenFileReplay(Event $event)
    {
        $context = $event->getSubject();
        $lesson = $context['lesson'];
        if ('liveOpen' != $lesson['type'] || ('liveOpen' == $lesson['type'] && 'videoGenerated' != $lesson['replayStatus'])) {
            return false;
        }

        $material = $this->getMaterialService()->searchMaterials(
            array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $lesson['id'],
                'source' => 'opencourselesson',
                'type' => 'openCourse',
            ),
            array('createdTime' => 'DESC'), 0, 1
        );

        if ($material) {
            $this->_resetExistMaterialLessonId($material[0]);
        }

        $fields = array(
            'courseId' => $lesson['courseId'],
            'lessonId' => $lesson['id'],
            'fileId' => $lesson['mediaId'],
            'source' => 'opencourselesson',
            'type' => 'openCourse',
            'courseSetId' => 0,
        );

        $this->getMaterialService()->uploadMaterial($fields);
    }

    private function _resetExistMaterialLessonId(array $material)
    {
        $updateFields = array('lessonId' => 0);

        $this->getMaterialService()->updateMaterial($material['id'],
            $updateFields, array('fileId' => $material['fileId'])
        );

        return true;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->getBiz()->service('Course:MaterialService');
    }
}
