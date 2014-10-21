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
            return null;
        }
        $exercise['questionTypeRange'] = json_decode($exercise['questionTypeRange'], true);
        return $exercise;
    }

    public function getExerciseByCourseIdAndLessonId($courseId, $lessonId)
    {
        $exercise = $this->getExerciseDao()->getExerciseByCourseIdAndLessonId($courseId, $lessonId);
        if (empty($exercise)) {
            return null;
        }
        $exercise['questionTypeRange'] = json_decode($exercise['questionTypeRange'], true);
        return $exercise;
    }

    public function createExercise($fields)
    {   
        if (!ArrayToolkit::requireds($fields, array('courseId', 'lessonId', 'questionCount', 'difficulty', 'ranges', 'source'))) {
            throw $this->createServiceException('参数缺失，创建练习失败！');
        }
        $exercise = $this->getExerciseByCourseIdAndLessonId($fields['courseId'], $fields['lessonId']);

        if (!empty($exercise)) {
            $this->getExerciseDao()->deleteExercise($exercise['id']);
        }

        $this->getLogService()->info('exercise', 'create', "创建练习(#{$exercise['id']})");

        return $this->getExerciseDao()->addExercise($this->filterExerciseFields($fields));
    }

    public function startExercise($id)
    {
        $exercise = $this->getExerciseDao()->getExercise($id);

        if (empty($exercise)) {
            throw $this->createServiceException('课时练习不存在！');
        }

        $course = $this->getCourseService()->getCourse($exercise['courseId']);
        if (empty($course)) {
            throw $this->createServiceException('练习所属课程不存在！');
        }

        $lesson = $this->getCourseService()->getCourseLesson($exercise['courseId'], $exercise['lessonId']);
        if (empty($lesson)) {
            throw $this->createServiceException('练习所属课时不存在！');
        }

        $user = $this->getCurrentUser();

        $exerciseResult = $this->getExerciseResultDao()->getExerciseResultByExerciseIdAndUserId($id,$user->id);
        
        if (!empty($homeworkResult)) {
            // $this->getExerciseResultDao()->deleteExerciseResult($exerciseResult['id']);
        }

        $result = $this->getHomeworkResultDao()->getExerciseResultByExerciseIdAndStatusAndUserId($id,$user->id, 'doing');
        if (empty($result)){
            $homeworkResult = array(
                'homeworkId' => $homework['id'],
                'courseId' => $homework['courseId'],
                'lessonId' =>  $homework['lessonId'],
                'userId' => $this->getCurrentUser()->id,
                'checkTeacherId' => $homework['createdUserId'],
                'status' => 'doing',
                'usedTime' => time(),
            );

            return $this->getHomeworkResultDao()->addHomeworkResult($homeworkResult);
        } else {
            return $result;
        }
    }

    public function updateExercise($id, $fields)
    {
        if (!ArrayToolkit::requireds($fields, array('courseId', 'lessonId', 'questionCount', 'difficulty', 'ranges', 'source'))) {
            throw $this->createServiceException('参数缺失，更新练习失败！');
        }

        $exercise = $this->getExercise($id);
        $exercise = $this->getExerciseDao()->updateExercise($exercise['id'], $this->filterExerciseFields($fields));

        $this->getLogService()->info('exercise', 'update', "编辑练习(#{$exercise['id']})");

        return $exercise;
    }

    public function deleteExercise($id)
    {
        $this->getExerciseDao()->deleteExercise($id);

        $this->getLogService()->info('exercise', 'delete', "删除练习(#{$id})");

        return true;
    }

    public function canBuildExercise($fields)
    {
        $questionsCount = count($this->getQuestions($fields));
        if ($questionsCount < $fields['questionCount']) {
            $lessNum = $fields['questionCount'] - $questionsCount;
            return array('status' => 'no', 'lessNum' => $lessNum);
        } else {
            return array('status' => 'yes');
        }
    }

    public function findExerciseByCourseIdAndLessonIds($courseId, $lessonIds)
    {
        $exercises = $this->getExerciseDao()->findExerciseByCourseIdAndLessonIds($courseId, $lessonIds);
        return ArrayToolkit::index($exercises, 'lessonId');
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

    private function getQuestions($fields)
    {
        $conditions = array();

        if (!empty($fields['difficulty'])) {
            $conditions['difficulty'] = $fields['difficulty'];
        }

        if ($fields['source'] == 'lesson') {
            $conditions['target'] = 'course-'.$fields['courseId'].'/lesson-'.$fields['lessonId'];
        } else {
            $conditions['targetPrefix'] = 'course-'.$fields['courseId'];
        }
        $conditions['types'] = $fields['ranges'];
        $conditions['parentId'] = 0;

        $total = $this->getQuestionService()->searchQuestionsCount($conditions);

        return $this->getQuestionService()->searchQuestions($conditions, array('createdTime', 'DESC'), 0, $total);
    }

    private function canBuildWithQuestions($fields, $questions)
    {
        $missing = array();

        foreach ($fields['counts'] as $type => $needCount) {
            $needCount = intval($needCount);
            if ($needCount == 0) {
                continue;
            }

            if (empty($questions[$type])) {
                $missing[$type] = $needCount;
                continue;
            }

            if (count($questions[$type]) < $needCount) {
                $missing[$type] = $needCount - count($questions[$type]);
            }
        }

        if (empty($missing)) {
            return array('status' => 'yes');
        }

        return array('status' => 'no', 'missing' => $missing);
    }

    private function getExerciseResultDao()
    {
        return $this->createDao('Course.ExerciseResultDao');
    }

    protected function getExerciseDao()
    {
        return $this->createDao('Course.ExerciseDao');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question.QuestionService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');        
    }

}