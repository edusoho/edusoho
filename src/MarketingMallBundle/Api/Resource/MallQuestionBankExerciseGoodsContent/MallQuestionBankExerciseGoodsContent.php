<?php

namespace MarketingMallBundle\Api\Resource\MallQuestionBankExerciseGoodsContent;

use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Common\GoodsContentBuilder\QuestionBankBuilder;

class MallQuestionBankExerciseGoodsContent extends BaseResource
{
    public function get(ApiRequest $request, $id)
    {
        $builder = new QuestionBankBuilder($this->biz);

        return $builder->build($id);
    }
}
