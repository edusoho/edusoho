<?php

namespace MarketingMallBundle\Common\GoodsContentBuilder;

use AppBundle\Common\ArrayToolkit;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class QuestionBankBuilder extends AbstractBuilder
{

    public function build($id)
    {
        $exercise = $this->getExerciseService()->get($id);

        if (empty($exercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }
        return [
                'bankId' => $id,
                'title' => $exercise['title'],
                'cover' => $this->transformCover($exercise['cover'], 'item_bank_exercise.png'),
                'price' => $exercise['price'],
                'questionBankCatalogue' => array_merge([$this->buildChapterExercise($exercise)], $this->buildAssessmentList($exercise)),
            ];
    }

    public function builds($ids)
    {
        $goodsContent = [];
        $exercises = $this->getExerciseService()->findByIds($ids);

        if (empty($exercises)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }

        foreach ($exercises as $exercise) {
            array_push($goodsContent, [
                'bankId' => $exercise['id'],
                'title' => $exercise['title'],
                'cover' => $this->transformCover($exercise['cover'], 'item_bank_exercise.png'),
                'price' => $exercise['price'],
                'questionBankCatalogue' => array_merge([$this->buildChapterExercise($exercise)], $this->buildAssessmentList($exercise)),
            ]);
        }

         return $goodsContent;

    }

    protected function buildChapterExercise($exercise)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']);
        $list = $exercise['chapterEnable'] ? $this->getItemCategoryService()->getItemCategoryTree($questionBank['itemBankId']) : [];
        $list = $this->buildChapterList($list);
        $num = $this->getItemService()->countItems(['bank_id' => $exercise['id'], 'category_id' => 0]);
        if ($num > 0) {
            $list[] = ['title' => '未分类',
                'questionCount' => $num];
        }

        return [
            'title' => '章节练习',
            'type' => 'chapter',
            'list' => $list,
        ];
    }

    protected function buildChapterList($list)
    {
        foreach ($list as &$chapter) {
            $chapter = [
                'title' => $chapter['name'],
                'questionCount' => $chapter['item_num'],
                'children' => $this->buildChapterList($chapter['children']),
            ];
            if (empty($chapter['children'])) {
                unset($chapter['children']);
            }
        }

        return $list;
    }

    protected function buildAssessmentList($exercise)
    {
        $modules = $this->getExerciseModuleService()->findByExerciseIdAndType($exercise['id'], ExerciseModuleService::TYPE_ASSESSMENT);
        $assessmentExercises = $this->getAssessmentExerciseService()->findByModuleIds(array_column($modules, 'id'));
        $assessments = $this->getAssessmentService()->searchAssessments(['ids' => array_column($assessmentExercises, 'assessmentId')], [], 0, count($assessmentExercises), ['id', 'name', 'item_count', 'total_score']);
        $assessments = array_column($assessments, null, 'id');
        $assessmentExercisesGroupByModuleId = ArrayToolkit::group($assessmentExercises, 'moduleId');
        $assessmentModuleList = [];
        foreach ($modules as $module) {
            $assessmentList = [];
            foreach ($assessmentExercisesGroupByModuleId[$module['id']] ?? [] as $assessmentExercise) {
                $assessment = $assessments[$assessmentExercise['assessmentId']];
                $assessmentList[] = [
                    'title' => $assessment['name'],
                    'questionCount' => $assessment['item_count'],
                    'totalScore' => $assessment['total_score'],
                ];
            }
            $assessmentModuleList[] = [
                'title' => $module['title'],
                'type' => 'assessment',
                'list' => $assessmentList,
            ];
        }

        return $assessmentModuleList;
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->biz->service('QuestionBank:QuestionBankService');
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->biz->service('ItemBank:Item:ItemCategoryService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->biz->service('ItemBank:Item:ItemService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getExerciseModuleService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return AssessmentExerciseService
     */
    protected function getAssessmentExerciseService()
    {
        return $this->biz->service('ItemBankExercise:AssessmentExerciseService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentService');
    }
}
