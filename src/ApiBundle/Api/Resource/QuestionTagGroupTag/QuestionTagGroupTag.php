<?php

namespace ApiBundle\Api\Resource\QuestionTagGroupTag;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\QuestionTag\Service\QuestionTagService;

class QuestionTagGroupTag extends AbstractResource
{
    /**
     * @Access(roles="ROLE_TEACHER", permissions="admin_v2")
     */
    public function search()
    {
        $tagGroups = $this->getQuestionTagService()->searchTagGroups(['status' => 1], ['id', 'name']);
        if (empty($tagGroups)) {
            return [];
        }
        $tags = $this->getQuestionTagService()->searchTags(['groupIds' => array_column($tagGroups, 'id'), 'status' => 1], ['id', 'groupId', 'name']);
        $tags = ArrayToolkit::group($tags, 'groupId');
        foreach ($tagGroups as &$tagGroup) {
            $tagGroup['tags'] = empty($tags[$tagGroup['id']]) ? [] : ArrayToolkit::thin($tags[$tagGroup['id']], ['id', 'name']);
        }

        return $tagGroups;
    }

    /**
     * @return QuestionTagService
     */
    private function getQuestionTagService()
    {
        return $this->getBiz()->service('QuestionTag:QuestionTagService');
    }
}
