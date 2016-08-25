<?php 
namespace Custom\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Course\CourseService;
use Topxia\Service\Course\Impl\CourseServiceImpl as BaseCourseServiceImpl;

class CourseServiceImpl extends BaseCourseServiceImpl implements CourseService
{
    public function startLearnLesson($courseId, $lessonId)
    {
        list($course, $member) = $this->tryTakeCourse($courseId);
        $user                  = $this->getCurrentUser();

        $lesson = $this->getCourseLesson($courseId, $lessonId);

        if (!empty($lesson)) {
            if ($lesson['type'] == 'video') {
                $createLessonView['courseId'] = $courseId;
                $createLessonView['lessonId'] = $lessonId;
                $createLessonView['fileId']   = $lesson['mediaId'];

                $file = array();

                if (!empty($createLessonView['fileId'])) {
                    $file = $this->getUploadFileService()->getFile($createLessonView['fileId']);
                }

                $createLessonView['fileStorage'] = empty($file) ? "net" : $file['storage'];
                $createLessonView['fileType']    = $lesson['type'];
                $createLessonView['fileSource']  = $lesson['mediaSource'];

                $this->createLessonView($createLessonView);
            }

            $learn = $this->getLessonLearnDao()->getLearnByUserIdAndLessonId($user['id'], $lessonId);

            if ($learn) {
                return false;
            }

            $learn = $this->getLessonLearnDao()->addLearn(array(
                'userId'       => $user['id'],
                'courseId'     => $courseId,
                'lessonId'     => $lessonId,
                'status'       => 'learning',
                'startTime'    => time(),
                'finishedTime' => 0
            ));

            $this->dispatchEvent(
                'course.lesson_start',
                new ServiceEvent($lesson, array('course' => $course, 'learn' => $learn))
            );

            return true;
        }

        return false;
    }

    protected function _filterCourseFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'title'          => '',
            'subtitle'       => '',
            'about'          => '',
            'expiryDay'      => 0,
            'serializeMode'  => 'none',
            'categoryId'     => 0,
            'vipLevelId'     => 0,
            'goals'          => array(),
            'audiences'      => array(),
            'tags'           => '',
            'startTime'      => 0,
            'endTime'        => 0,
            'locationId'     => 0,
            'address'        => '',
            'maxStudentNum'  => 0,
            'watchLimit'     => 0,
            'approval'       => 0,
            'maxRate'        => 0,
            'locked'         => 0,
            'tryLookable'    => 0,
            'tryLookTime'    => 0,
            'buyable'        => 0,
            'conversationId' => '',
            'orgCode'        => '',
            'orgId'          => '',
            'studyModel'    => 'normal'
        ));

        if (!empty($fields['tags'])) {
            $fields['tags'] = explode(',', $fields['tags']);
            $fields['tags'] = $this->getTagService()->findTagsByNames($fields['tags']);
            array_walk($fields['tags'], function (&$item, $key) {
                $item = (int) $item['id'];
            }

            );
        }

        return $fields;
    }
}