<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\WrongBook\Service\WrongQuestionService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;

class WrongBookTeacherDetail extends AbstractResource
{
    public function search(ApiRequest $request, $itemId)
    {
        $conditions = $request->query->all();
        $stem = $this->getQuestionDao()->findByItemId($itemId);
        $sceneIds = $this->findSceneIdsByCourseId($conditions['target_id']);
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $wrongQuestions=$this->getWrongQuestionService()->getWrongBookQuestionByFields(['answer_scene_ids'=>$sceneIds,'item_id'=>$itemId]);

        $answerQuestionReportIds=ArrayToolkit::column($wrongQuestions,'answer_question_report_id');
        $reports=$this->getAnswerQuestionReportService()->findByIds($answerQuestionReportIds);
        foreach ($wrongQuestions as &$wrongQuestion){
            $wrongQuestion['response']=$reports[$wrongQuestion['answer_question_report_id']]['response'];

        }
        $wrongQuestions=ArrayToolkit::group($wrongQuestions,'user_id');
        $questions=$this->getWrongQuestionService()->searchWrongBookQuestionsByConditions(
            ['answer_scene_ids'=>$sceneIds,'item_id'=>$itemId],
            [],
            $offset,
            $limit
        );
        
        foreach ($questions as &$question){
            $question['wrong_log']=$wrongQuestions[$question['user_id']];
            $question['response']=$reports[$question['answer_question_report_id']]['response'];

        }
        $total = $this->service('WrongBook:WrongQuestionService')->countWrongBookQuestionsByConditions(['answer_scene_ids'=>$sceneIds,'item_id'=>$itemId]);
        $questionLogs['stem']=$stem;
        $questionLogs['data']=$questions;

        return $this->makePagingObject($questionLogs, $total, $offset, $limit);
    }
    protected function findSceneIdsByCourseId($courseSetId)
    {
        $activityTestPapers = $this->getActivityService()->findActivitiesByCourseSetIdAndType($courseSetId, 'testpaper', true);
        $activityHomeWorks = $this->getActivityService()->findActivitiesByCourseSetIdAndType($courseSetId, 'homework', true);
        $activityExercises = $this->getActivityService()->findActivitiesByCourseSetIdAndType($courseSetId, 'exercise', true);
        $activates = array_merge($activityTestPapers, $activityHomeWorks, $activityExercises);

        return $this->generateSceneIds($activates);
    }

    protected function generateSceneIds($activates)
    {
        $sceneIds = [];
        array_walk($activates, function ($activity) use (&$sceneIds) {
            if (!empty($activity['ext'])) {
                $sceneIds[] = $activity['ext']['answerSceneId'];
            }
        });

        return $sceneIds;
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->biz->dao('ItemBank:Item:QuestionDao');
    }

    /**
     * @return WrongQuestionService
     */
    private function getWrongQuestionService()
    {
        return $this->service('WrongBook:WrongQuestionService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return  $this->service('Activity:ActivityService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->service('ItemBank:Answer:AnswerQuestionReportService');
    }


}
