<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\ExerciseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;

class ExerciseServiceImpl extends BaseService implements ExerciseService
{

    public function getExercise($id)
    {
        return $this->getExerciseDao()->getExercise($id);
    }

    public function createExercise($fields)
    {   
        if (!ArrayToolkit::requireds($fields, array('courseId', 'lessonId', 'questionCount', 'difficulty', 'ranges'))) {
            throw $this->createServiceException('参数缺失，创建练习失败！');
        }
        $exercise = $this->getExerciseDao()->addExercise($this->filterExerciseFields($fields, 'create'));
        $items = $this->buildExercise($exercise['id'], $fields);

        return array($exercise, $items);
    }

    public function findExerciseByCourseIdAndLessonIds($courseId, $lessonIds)
    {
        $exercises = $this->getExerciseDao()->findExerciseByCourseIdAndLessonIds($courseId, $lessonIds);
        return ArrayToolkit::index($exercises, 'id');
    }

    public function buildExercise($id, $options)
    {
        $exercise = $this->getExerciseDao()->getExercise($id);
        if (empty($exercise)) {
            throw $this->createServiceException("Exercise #{$id} is not found.");
        }

        $this->getExerciseItemDao()->deleteItemsByExerciseId($exercise['id']);

        $questions = $this->getQuestions($options);
        if (empty($questions)) {
            throw $this->createServiceException("Questions is empty.");
        }

        $items = array();
        $seq = 1;

        foreach ($questions as $item) {
            $fields = array();
            $fields['exerciseId'] = $exercise['id'];
            $fields['seq'] = $seq;
            $fields['questionId'] = $item['id'];
            $fields['questionType'] = $item['type'];

            $seq++;
            $items[] = $this->getExerciseItemDao()->addItem($fields);
        }

        $this->getExerciseDao()->updateExercise($exercise['id'], array( 'itemCount' => $seq -1));

        return $items;
    }

    public function filterExerciseFields($fields, $mode)
    {
        $filtedFields = array();
        if ($mode == 'create') {
            $filtedFields['itemCount'] = $fields['questionCount'];
            $filtedFields['source'] = $fields['source'];
            $filtedFields['courseId'] = $fields['courseId'];
            $filtedFields['lessonId'] = $fields['lessonId'];
            $filtedFields['difficulty'] = empty($fields['difficulty']) ? '' : $fields['difficulty'];
            $filtedFields['questionTypeRange'] = json_encode($fields['ranges']);
            $filtedFields['createdUserId'] = $this->getCurrentUser()->id;
            $filtedFields['createdTime']   = time();
        } else {
            if (array_key_exists('name', $fields)) {
                $filtedFields['name'] = empty($fields['name']) ? '' : $fields['name'];
            }

            if (array_key_exists('description', $fields)) {
                $filtedFields['description'] = empty($fields['description']) ? '' : $fields['description'];
            }

            if (array_key_exists('limitedTime', $fields)) {
                $filtedFields['limitedTime'] = empty($fields['limitedTime']) ? 0 : (int) $fields['limitedTime'];
            }
        }

        return $filtedFields;
    }

    private function getQuestions($options)
    {
        $conditions = array();
        $questionCount = $options['questionCount'];

        if ($options['source'] == 'course') {
            $options['targets'][] = "course-{$options['courseId']}/lesson-{$options['lessonId']}";
            $options['targets'][] = "course-{$options['courseId']}";
        } else {
            $options['target'] = "course-{$options['courseId']}/lesson-{$options['lessonId']}";
        }

        if (!empty($options['difficulty'])) {
            $conditions['difficulty'] = $options['difficulty'];
        }

        if (!empty($options['target'])) {
            $conditions['target'] = $options['target'];
        }

        if (!empty($options['ranges'])) {
            $conditions['types'] =  $options['ranges'];
        }

        $conditions['parentId'] = 0;

        return $this->getQuestionService()->searchQuestions($conditions, array('createdTime', 'DESC'), 0, $questionCount);
    }

    private function getExerciseDao()
    {
        return $this->createDao('Course.ExerciseDao');
    }

    private function getExerciseItemDao()
    {
        return $this->createDao('Course.ExerciseItemDao');
    }

    private function getQuestionService()
    {
        return $this->createService('Question.QuestionService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

}