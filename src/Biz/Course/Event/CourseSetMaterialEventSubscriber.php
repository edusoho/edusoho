<?php

namespace Biz\Course\Event;

use AppBundle\Common\ArrayToolkit;
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
            'course.activity.create' => 'onCourseActivityCreate',
            'course.activity.delete' => 'onCourseActivityDelete',
            'course.activity.update' => 'onCourseActivityUpdate',
            'upload.file.delete' => 'onUploadFileDelete',
            'upload.file.finish' => 'onUploadFileFinish',
            'upload.file.add' => 'onUploadFileFinish',

            //TODO
            // 'course-set.material.create' => 'onMaterialCreate',
            // 'course-set.material.update' => 'onMaterialUpdate',
            // 'course-set.material.delete' => 'onMaterialDelete'

            'open.course.delete' => 'onOpenCourseDelete',
            'open.course.lesson.create' => 'onOpenCourseLessonCreate',
            'open.course.lesson.update' => 'onOpenCourseLessonUpdate',
            'open.course.lesson.delete' => 'onOpenCourseLessonDelete',

            //'course.lesson.generate.video.replay'      => 'onLiveFileReplay',
            'open.course.lesson.generate.video.replay' => 'onLiveOpenFileReplay',
        );
    }

    public function onCourseSetDelete(Event $event)
    {
        $courseSet = $event->getSubject();
        $this->getMaterialService()->deleteMaterialsByCourseSetId($courseSet['id']);

        //FIXME TagOwner ?
        // $tagOwnerManager = new TagOwnerManager('course-set', $courseSet['id']);
        // $tagOwnerManager->delete();
    }

    public function onCourseDelete(Event $event)
    {
        $course = $event->getSubject();
        $this->getMaterialService()->deleteMaterialsByCourseId($course['id']);

        $tagOwnerManager = new TagOwnerManager('course', $course['id']);
        $tagOwnerManager->delete();
    }

    public function onCourseActivityCreate(Event $event)
    {
        $context = $event->getSubject();
        $argument = $context['argument'];
        $activity = $context['activity'];

        if (in_array($activity['type'], array('testpaper', 'live', 'text')) || !$activity['mediaId']) {
            return false;
        }

        $material = $this->getMaterialService()->searchMaterials(
            array(
                'courseId' => $activity['courseId'],
                'courseSetId' => $activity['courseSetId'],
                'lessonId' => $activity['id'],
                'fileId' => $activity['mediaId'],
                'source' => 'courseactivity',
            ),
            array('createdTime' => 'DESC'),
            0,
            1
        );
        if (!$material) {
            $fields = array(
                'courseSetId' => $activity['courseSetId'],
                'courseId' => $activity['courseId'],
                'lessonId' => $activity['id'],
                'fileId' => $activity['mediaId'],
                'source' => 'courseactivity',
            );
            $this->getMaterialService()->uploadMaterial($fields);
        }
    }

    public function onCourseActivityDelete(Event $event)
    {
        $context = $event->getSubject();
        $activity = $context['activity'];
        $courseId = $context['courseId'];

        $materials = $this->getMaterialService()->searchMaterials(
            array(
                'courseId' => $activity['courseId'],
                'lessonId' => $activity['id'],
                'type' => 'course',
            ),
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        if (!$materials) {
            return false;
        }

        foreach ($materials as $key => $material) {
            if ($material['fileId'] == 0 && !empty($material['link'])) {
                $this->getMaterialService()->deleteMaterial($material['courseId'], $material['id']);
            } else {
                $updateFields = array(
                    'courseId' => 0,
                    'lessonId' => 0,
                );
                $this->getMaterialService()->updateMaterial($material['id'], $updateFields, array('fileId' => $material['fileId']));
            }
        }
    }

    public function onCourseActivityUpdate(Event $event)
    {
        $context = $event->getSubject();
        $argument = $context['argument'];
        $activity = $context['activity'];
        $sourceActivity = $context['sourceActivity'];

        if (in_array($activity['type'], array('text', 'testpaper', 'live')) ||
            ($activity['mediaId'] == $sourceActivity['mediaId'])
        ) {
            return false;
        }

        $material = $this->getMaterialService()->searchMaterials(
            array(
                'courseId' => $activity['courseId'],
                'lessonId' => $activity['id'],
                'source' => 'courseactivity',
                'type' => 'course',
            ),
            array('createdTime' => 'DESC'),
            0,
            1
        );

        if ($material) {
            if ($activity['mediaId'] != 0 && $activity['mediaSource'] == 'self') {
                $this->_resetExistMaterialLessonId($material[0]);

                $fields = array(
                    'courseId' => $activity['courseId'],
                    'lessonId' => $activity['id'],
                    'fileId' => $activity['mediaId'],
                    'source' => 'courseactivity',
                    'type' => 'course',
                );
                $this->getMaterialService()->uploadMaterial($fields);
            } elseif ($activity['mediaSource'] != 'self' && $activity['mediaId'] == 0) {
                $this->_resetExistMaterialLessonId($material[0]);
            }
        } else {
            $fields = array(
                'courseId' => $activity['courseId'],
                'lessonId' => $activity['id'],
                'fileId' => $activity['mediaId'],
                'source' => 'courseactivity',
                'type' => 'course',
            );
            $this->getMaterialService()->uploadMaterial($fields);
        }
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
            if ($material['source'] == 'coursematerial' && $material['lessonId']) {
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

    public function onMaterialCreate(Event $event)
    {
        $context = $event->getSubject();
        $argument = $context['argument'];
        $material = $context['material'];

        if ($material['type'] == 'openCourse') {
            return false;
        }

        $courses = $this->getCourseService()->findCoursesByParentIdAndLocked($material['courseId'], 1);
        $courseIds = ArrayToolkit::column($courses, 'id');

        if ($courseIds) {
            $lessons = $this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($material['lessonId'], $courseIds);
            $lessonIds = ArrayToolkit::column($lessons, 'id');
            $argument['copyId'] = $material['id'];

            foreach ($courseIds as $key => $courseId) {
                $argument['courseId'] = $courseId;
                $argument['lessonId'] = isset($lessonIds[$key]) ? $lessonIds[$key] : 0;

                $this->getMaterialService()->uploadMaterial($argument);
            }
        }
    }

    public function onMaterialUpdate(Event $event)
    {
        $context = $event->getSubject();
        $argument = $context['argument'];
        $material = $context['material'];

        if ($material['type'] == 'openCourse') {
            return false;
        }

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($material['courseId'], 1), 'id');

        if ($courseIds) {
            $copyMaterials = $this->getMaterialService()->findMaterialsByCopyIdAndLockedCourseIds($material['id'], $courseIds);

            foreach ($copyMaterials as $key => $copyMaterial) {
                if ($material['lessonId']) {
                    $parentMaterial = $this->getMaterialService()->getMaterial($material['courseId'], $copyMaterial['copyId']);
                    $copyLesson = $this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($parentMaterial['lessonId'], array($copyMaterial['courseId']));

                    $this->getMaterialService()->updateMaterial($copyMaterial['id'], array('lessonId' => $copyLesson[0]['id']), $argument);
                } else {
                    $this->getMaterialService()->updateMaterial($copyMaterial['id'], array('lessonId' => 0), $argument);
                }
            }
        }
    }

    public function onMaterialDelete(Event $event)
    {
        $material = $event->getSubject();

        if ($material['type'] == 'openCourse') {
            return false;
        }

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($material['courseId'], 1), 'id');

        if ($courseIds) {
            $materialIds = ArrayToolkit::column($this->getMaterialService()->findMaterialsByCopyIdAndLockedCourseIds($material['id'], $courseIds), 'id');

            foreach ($materialIds as $key => $materialId) {
                $this->getMaterialService()->deleteMaterial($courseIds[$key], $materialId);
            }
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
            if ($lesson['mediaId'] != 0 && $lesson['mediaSource'] == 'self') {
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
            } elseif ($lesson['mediaSource'] != 'self' && $lesson['mediaId'] == 0) {
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
            if ($material['fileId'] == 0 && !empty($material['link'])) {
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

        if ($lesson['type'] != 'live' || ($lesson['type'] == 'live' && $lesson['replayStatus'] != 'videoGenerated')) {
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

        if ($lesson['type'] != 'liveOpen' || ($lesson['type'] == 'liveOpen' && $lesson['replayStatus'] != 'videoGenerated')) {
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
