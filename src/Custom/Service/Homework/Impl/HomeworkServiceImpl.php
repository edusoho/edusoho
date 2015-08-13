<?php

namespace Custom\Service\Homework\Impl;

use Custom\Service\Homework\HomeworkService;
use Homework\Service\Homework\Impl\HomeworkServiceImpl as BaseHomeworkServiceImpl;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Common\ArrayToolkit;

class HomeworkServiceImpl extends BaseHomeworkServiceImpl implements HomeworkService
{
    public function createHomework($courseId, $lessonId, $fields)
    {
        if (empty($fields)) {
            throw$this->createServiceException("内容为空，创建作业失败！");
        }

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException('课程不存在，创建作业失败！');
        }

        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            throw $this->createServiceException('课时不存在，创建作业失败！');
        }

        $excludeIds = $fields['excludeIds'];

        if (empty($excludeIds)) {
            throw $this->createServiceException("题目不能为空，创建作业失败！");
        }
        if (!empty($fields['pairReview']) and $fields['pairReview'] == 1) {
            $completeTime = strtotime($fields['completeTime']);
            $reviewEndTime = strtotime($fields['reviewEndTime']);
            $fields['completeTime'] = $completeTime;
            $fields['reviewEndTime'] = $reviewEndTime;
        }

        unset($fields['excludeIds']);

        $fields = $this->filterHomeworkFields($fields, $mode = 'add');
        $fields['courseId'] = $courseId;
        $fields['lessonId'] = $lessonId;
        $excludeIds = explode(',', $excludeIds);
        $fields['itemCount'] = count($excludeIds);
        $fields['updatedUserId'] = 0;
        $fields['updatedTime'] = 0;
        $homework = $this->getHomeworkDao()->addHomework($fields);
        $this->addHomeworkItems($homework['id'], $excludeIds);

        $this->getLogService()->info('homework', 'create', '创建课程{$courseId}课时{$lessonId}的作业');

        return $homework;
    }

    public function updateHomework($id, $fields)
    {
        $homework = $this->getHomework($id);

        if (empty($homework)) {
            throw $this->createServiceException('作业不存在，更新作业失败！');
        }
        if ($fields['pairReview'] == 1) {
            $fields['completeTime'] = strtotime($fields['completeTime']);
            $fields['reviewEndTime'] = strtotime($fields['reviewEndTime']);
        } else {
            $fields['completeTime'] = 0;
            $fields['reviewEndTime'] = 0;
        }

        $fields = $this->filterHomeworkFields($fields, $mode = 'edit');

        $homework = $this->getHomeworkDao()->updateHomework($id, $fields);

        $this->getLogService()->info('homework', 'update', '更新课程{$courseId}课时{$lessonId}的{$id}作业');

        return $homework;


    }

    public function createHomeworkPairReview($homeworkResultId, array $fields)
    {
        $homeworkResult = $this->loadHomeworkResult($homeworkResultId);
        $fields['homeworkResultId'] = $homeworkResult['id'];
        $fields['homeworkId'] = $homeworkResult['homeworkId'];
        $fields['userId'] = $this->getCurrentUser()->id;
        return $this->getReviewDao()->create($fields);
    }

    public function randomizeHomeworkResultForPairReview($homeworkId, $userId)
    {
        $homework = $this->getHomeworkDao()->getHomework($homeworkId);
        $reviewableResults = $this->getResultDao()->findPairReviewables($homework, $userId);
        if (empty($reviewableResults)) {
            return null;
        }
        $selectedId = $this->pickReviewable($reviewableResults, $homework);
        $result = $this->loadHomeworkResult($selectedId);
        $resultItems = $this->getItemSetResultByHomeworkIdAndUserId($homeworkId, $result['userId']);
        $result['items'] = $resultItems;
        return $result;
    }

    public function getHomeworkResult($homeworkResultId)
    {
        return $this->getResultDao()->getResult($homeworkResultId);
    }

    public function loadHomeworkResult($homeworkResultId)
    {
        if (empty($homeworkResultId)) {
            throw $this->createServiceException("作业答卷id为空.");
        }
        $homeworkResult = $this->getHomeworkResult($homeworkResultId);
        if (empty($homeworkResult)) {
            throw $this->createServiceException("未能找到作业答卷！");
        }
        return $homeworkResult;
    }

    public function updateHomeworkResult($homeworkResultId, array $fields)
    {
        return $this->getResultDao()->updateResult($homeworkResultId, $fields);
    }

    private function pickReviewable($results, $homework)
    {
        $insufficient = array();
        $sufficient = array();
        foreach ($results as $result) {
            if ($result['pairReviews'] < $homework['minReviews']) {
                array_push($insufficient, $result['id']);
            } else {
                array_push($sufficient, $result['id']);
            }
        }
        $ids = empty($insufficient) ? $sufficient : $insufficient;
        return empty($ids) ? null : $ids[rand(0, count($ids) - 1)];
    }

    protected function getReviewDao()
    {
        return $this->createDao('Custom:Homework.ReviewDao');
    }

    protected function getHomeworkDao()
    {
        return $this->createDao('Homework:Homework.HomeworkDao');
    }

    protected function getResultDao()
    {
        return $this->createDao('Custom:Homework.HomeworkResultDao');
    }

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

    private function getQuestionService()
    {
        return $this->createService('Question.QuestionService');
    }

    private function getHomeworkItemDao()
    {
        return $this->createDao('Homework:Homework.HomeworkItemDao');
    }

    private function filterHomeworkFields($fields, $mode)
    {
        $fields['description'] = $fields['description'];

        if ($mode == 'add') {
            $fields['createdUserId'] = $this->getCurrentUser()->id;
            $fields['createdTime'] = time();
        }

        if ($mode == 'edit') {
            $fields['updatedUserId'] = $this->getCurrentUser()->id;
            $fields['updatedTime'] = time();
        }

        return $fields;
    }

    private function addHomeworkItems($homeworkId, $excludeIds)
    {
        $homeworkItems = array();
        $homeworkItemsSub = array();
        $includeItemsSubIds = array();
        $index = 1;

        foreach ($excludeIds as $key => $excludeId) {

            $questions = $this->getQuestionService()->findQuestionsByParentId($excludeId);

            $items['seq'] = $index++;
            $items['questionId'] = $excludeId;
            $items['homeworkId'] = $homeworkId;
            $items['parentId'] = 0;
            $homeworkItems[] = $this->getHomeworkItemDao()->addItem($items);

            if (!empty($questions)) {
                $i = 1;
                foreach ($questions as $key => $question) {
                    $items['seq'] = $i++;
                    $items['questionId'] = $question['id'];
                    $items['homeworkId'] = $homeworkId;
                    $items['parentId'] = $question['parentId'];
                    $homeworkItems[] = $this->getHomeworkItemDao()->addItem($items);
                }
            }
        }
    }
}