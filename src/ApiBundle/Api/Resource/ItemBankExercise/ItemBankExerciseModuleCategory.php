<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\ItemBankExercise\Service\ChapterExerciseService;

class ItemBankExerciseModuleCategory extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $exerciseId, $moduleId)
    {
        $itemBankExercise = $this->getItemBankExerciseService()->get($exerciseId);
        if (empty($itemBankExercise)) {
            return [];
        }

        return $this->getItemBankChapterExerciseService()->getPublishChapterTreeList($itemBankExercise['questionBankId']);
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ChapterExerciseService
     */
    protected function getItemBankChapterExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ChapterExerciseService');
    }
}
