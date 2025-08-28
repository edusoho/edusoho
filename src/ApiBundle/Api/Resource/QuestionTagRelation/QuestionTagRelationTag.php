<?php

namespace ApiBundle\Api\Resource\QuestionTagRelation;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\QuestionTag\Service\QuestionTagService;

class QuestionTagRelationTag extends AbstractResource
{
    public function search(ApiRequest $request, $itemId)
    {
        $relations = $this->getQuestionTagService()->findTagRelationsByItemIds([$itemId]);
        if (empty($relations)) {
            return [];
        }
        $tags = $this->getQuestionTagService()->searchTags(['ids' => array_column($relations, 'tagId')], ['id', 'groupId', 'name']);
        $groupTags = ArrayToolkit::group($tags, 'groupId');
        $finalTags = [];
        foreach ($groupTags as $groupId => $tags) {
            $finalTags[] = [
                'groupId' => $groupId,
                'tags' => ArrayToolkit::thin($tags, ['id', 'name']),
            ];
        }

        return $finalTags;
    }

    /**
     * @return QuestionTagService
     */
    private function getQuestionTagService()
    {
        return $this->getBiz()->service('QuestionTag:QuestionTagService');
    }
}
