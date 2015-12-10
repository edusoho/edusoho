<?php
namespace Mooc\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Course\Impl\CourseCopyServiceImpl as BaseCourseCopyService;

class CourseCopyServiceImpl extends BaseCourseCopyService
{
    public function copy($course, $link = false)
    {
        $this->getCourseDao()->getConnection()->beginTransaction();
        try {
            $newCourse = $this->copyCourse($course, $link);

            $newTeachers = $this->copyTeachers($course['id'], $newCourse);

            $newChapters = $this->copyChapters($course['id'], $newCourse);

            $newLessons = $this->copyLessons($course['id'], $newCourse, $newChapters);

            $newQuestions = $this->copyQuestions($course['id'], $newCourse, $newLessons);

            $newTestpapers = $this->copyTestpapers($course['id'], $newCourse, $newQuestions);

            $this->convertTestpaperLesson($newLessons, $newTestpapers);

            $newMaterials = $this->copyMaterials($course['id'], $newCourse, $newLessons);

            $code           = 'Homework';
            $homework       = $this->getAppService()->findInstallApp($code);
            $isCopyHomework = $homework && version_compare($homework['version'], "1.0.2", ">=");

            if ($isCopyHomework) {
                $newHomeworks = $this->copyHomeworks($course['id'], $newCourse, $newLessons, $newQuestions);
                $newExercises = $this->copyExercises($course['id'], $newCourse, $newLessons);
            }

            $this->getCourseDao()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getCourseDao()->getConnection()->rollback();
            throw $e;
        }

        return $newCourse;
    }

    protected function copyHomeworks($courseId, $newCourse, $newLessons, $newQuestions)
    {
        $homeworks = $this->getHomeworkDao()->findHomeworksByCourseId($courseId);

        $map = array();

        foreach ($homeworks as $homework) {
            $fields = ArrayToolkit::parts($homework, array(
                'description', 'itemCount', 'createdUserId', 'updatedUserId', 'correctPercent',
                'type', 'endTime', 'minEvaluateNum', 'evaluateEndTime', 'completePercent',
                'partPercent', 'nonePercent'
            ));

            $fields['courseId'] = $newCourse['id'];

            if ($homework['lessonId']) {
                $fields['lessonId'] = $newLessons[$homework['lessonId']]['id'];
            } else {
                $fields['lessonId'] = 0;
            }

            $fields['createdTime'] = time();
            $fields['updatedTime'] = time();
            $fields['copyId']      = $homework['id'];
            $newHomework           = $this->getHomeworkDao()->addHomework($fields);
            $map[$homework['id']]  = $newHomework;

            $items = $this->getHomeworkItemDao()->findItemsByHomeworkId($homework['id']);

            foreach ($items as $item) {
                $fields = array(
                    'homeworkId' => $newHomework['id'],
                    'seq'        => $item['seq'],
                    'questionId' => empty($newQuestions[$item['questionId']]['id']) ? 0 : $newQuestions[$item['questionId']]['id'],
                    'score'      => $item['score'],
                    'missScore'  => $item['missScore'],
                    'parentId'   => empty($newQuestions[$item['parentId']]['id']) ? 0 : $newQuestions[$item['parentId']]['id']
                );

                $this->getHomeworkItemDao()->addItem($fields);
            }
        }

        return $map;
    }
}
