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
        $tags = $this->getQuestionTagService()->searchTags(['ids' => array_column($relations, 'tagId'), 'status' => 1], ['id', 'groupId', 'name']);
        if (empty($tags)) {
            return [];
        }
        $tagGroups = $this->getQuestionTagService()->searchTagGroups(['ids' => array_column($tags, 'groupId'), 'status' => 1], ['id']);
        if (empty($tagGroups)) {
            return [];
        }
        $groupTags = ArrayToolkit::group($tags, 'groupId');
        $finalTags = [];
        foreach ($tagGroups as $tagGroup) {
            $finalTags[] = [
                'groupId' => $tagGroup['id'],
                'tags' => ArrayToolkit::thin($groupTags[$tagGroup['id']], ['id', 'name']),
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
