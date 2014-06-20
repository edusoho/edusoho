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
        $exercise = $this->getExerciseDao()->getExercise($id);
        if (empty($exercise)) {
            throw $this->createServiceException("Exercise #{$id} is not found.");
        }
        $exercise['questionTypeRange'] = json_decode($exercise['questionTypeRange'], true);
        return $exercise;
    }

    public function createExercise($fields)
    {   
        if (!ArrayToolkit::requireds($fields, array('courseId', 'lessonId', 'questionCount', 'difficulty', 'ranges', 'source'))) {
            throw $this->createServiceException('参数缺失，创建练习失败！');
        }
        $exercise = $this->getExerciseDao()->getExerciseByCourseIdAndLessonId($fields['courseId'], $fields['lessonId']);

        if (!empty($exercise)) {
            $this->getExerciseDao()->deleteExercise($exercise['id']);
            $this->getExerciseItemDao()->deleteItemsByExerciseId($exercise['id']);
        }
        $exercise = $this->getExerciseDao()->addExercise($this->filterExerciseFields($fields));
        $items = $this->buildExercise($exercise['id'], $fields);

        $this->getLogService()->info('exercise', 'create', "创建练习(#{$exercise['id']})");

        return array($exercise, $items);
    }

    public function updateExercise($id, $fields)
    {
        if (!ArrayToolkit::requireds($fields, array('courseId', 'lessonId', 'questionCount', 'difficulty', 'ranges', 'source'))) {
            throw $this->createServiceException('参数缺失，更新练习失败！');
        }

        $exercise = $this->getExercise($id);
        $this->getExerciseItemDao()->deleteItemsByExerciseId($exercise['id']);
        $exercise = $this->getExerciseDao()->updateExercise($exercise['id'], $this->filterExerciseFields($fields));
        $items = $this->buildExercise($exercise['id'], $fields);

        $this->getLogService()->info('exercise', 'update', "编辑练习(#{$exercise['id']})");

        return array($exercise ,$items);
    }

    public function deleteExercise($id)
    {
        $this->getExerciseDao()->deleteExercise($id);
        $this->getExerciseItemDao()->deleteItemsByExerciseId($id);

        $this->getLogService()->info('exercise', 'delete', "删除练习(#{$exercise['id']})");

        return true;
    }

    public function findExerciseByCourseIdAndLessonIds($courseId, $lessonIds)
    {
        $exercises = $this->getExerciseDao()->findExerciseByCourseIdAndLessonIds($courseId, $lessonIds);
        return ArrayToolkit::index($exercises, 'lessonId');
    }

    public function buildExercise($id, $options)
    {
        $exercise = $this->getExercise($id);

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

    public function filterExerciseFields($fields)
    {
        $filtedFields = array();    
        $filtedFields['itemCount'] = $fields['questionCount'];
        $filtedFields['source'] = $fields['source'];
        $filtedFields['courseId'] = $fields['courseId'];
        $filtedFields['lessonId'] = $fields['lessonId'];
        $filtedFields['difficulty'] = empty($fields['difficulty']) ? '' : $fields['difficulty'];
        $filtedFields['questionTypeRange'] = json_encode($fields['ranges']);
        $filtedFields['createdUserId'] = $this->getCurrentUser()->id;
        $filtedFields['createdTime']   = time();
       
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

    protected function getExerciseDao()
    {
        return $this->createDao('Course.ExerciseDao');
    }

    protected function getExerciseItemDao()
    {
        return $this->createDao('Course.ExerciseItemDao');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question.QuestionService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');        
    }

}