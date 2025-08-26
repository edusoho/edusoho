<?php

namespace Biz\QuestionTag\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\QuestionTag\Dao\QuestionTagDao;
use Biz\QuestionTag\Dao\QuestionTagGroupDao;
use Biz\QuestionTag\Dao\QuestionTagRelationDao;
use Biz\QuestionTag\Exception\QuestionTagException;
use Biz\QuestionTag\Service\QuestionTagService;
use Biz\System\Service\LogService;

class QuestionTagServiceImpl extends BaseService implements QuestionTagService
{
    public function getTagGroupByName($name)
    {
        return $this->getQuestionTagGroupDao()->getByName($name);
    }

    public function createTagGroup($name)
    {
        if (empty($name)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }
        $tagGroup = $this->getTagGroupByName($name);
        if (!empty($tagGroup)) {
            $this->createNewException(QuestionTagException::TAG_GROUP_NAME_DUPLICATE());
        }
        $this->getQuestionTagGroupDao()->create([
            'name' => $name,
            'seq' => $this->getQuestionTagGroupDao()->count([]) + 1,
        ]);
        $this->getLogService()->info('question_tag', 'add_tag_group', "添加题目标签类型：{$name}");
    }

    public function updateTagGroup($id, $params)
    {
        $tagGroup = $this->getQuestionTagGroupDao()->get($id);
        if (empty($tagGroup)) {
            $this->createNewException(QuestionTagException::TAG_GROUP_NOT_FOUND());
        }
        $params = ArrayToolkit::parts($params, ['name', 'status']);
        if (!empty($params['name'])) {
            $tagGroup = $this->getTagGroupByName($params['name']);
            if (!empty($tagGroup) && $tagGroup['id'] != $id) {
                $this->createNewException(QuestionTagException::TAG_GROUP_NAME_DUPLICATE());
            }
        }
        $this->getQuestionTagGroupDao()->update($id, $params);
    }

    public function deleteTagGroup($id)
    {
        $tagGroup = $this->getQuestionTagGroupDao()->get($id);
        if (empty($tagGroup)) {
            return;
        }
        try {
            $this->beginTransaction();
            $this->getQuestionTagGroupDao()->delete($id);
            $tagGroups = $this->searchTagGroups([]);
            if (!empty($tagGroups)) {
                $this->sortTagGroups(array_column($tagGroups, 'id'));
            }
            $tags = $this->searchTags(['groupId' => $id]);
            if (!empty($tags)) {
                $this->getQuestionTagDao()->batchDelete(['groupId' => $id]);
                $this->getQuestionTagRelationDao()->batchDelete(['tagIds' => array_column($tags, 'id')]);
            }
            $this->getLogService()->info('question_tag', 'delete_tag_group', "删除题目标签类型：{$tagGroup['name']}");
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogger()->error("delete question tag group {$tagGroup['name']} error: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function searchTagGroups($conditions, $columns = [])
    {
        return $this->getQuestionTagGroupDao()->search($conditions, ['seq' => 'ASC'], 0, PHP_INT_MAX, $columns);
    }

    public function sortTagGroups($ids)
    {
        if (empty($ids)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }
        $tagGroups = $this->searchTagGroups([]);
        if (!ArrayToolkit::isSameValues($ids, array_column($tagGroups, 'id'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }
        $seqs = [];
        foreach ($ids as $key => $id) {
            $seqs[$id] = ['seq' => $key + 1];
        }
        $this->getQuestionTagGroupDao()->batchUpdate($ids, $seqs);
    }

    public function getTagByGroupIdAndName($groupId, $name)
    {
        return $this->getQuestionTagDao()->getByGroupIdAndName($groupId, $name);
    }

    public function createTag($params)
    {
        if (!ArrayToolkit::requireds($params, ['name', 'groupId'], true)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        $tagGroup = $this->getQuestionTagGroupDao()->get($params['groupId']);
        if (empty($tagGroup)) {
            $this->createNewException(QuestionTagException::TAG_GROUP_NOT_FOUND());
        }
        $tag = $this->getTagByGroupIdAndName($params['groupId'], $params['name']);
        if (!empty($tag)) {
            $this->createNewException(QuestionTagException::TAG_NAME_DUPLICATE());
        }
        $this->getQuestionTagDao()->create([
            'groupId' => $params['groupId'],
            'name' => $params['name'],
            'seq' => $this->getQuestionTagDao()->count(['groupId' => $params['groupId']]) + 1,
        ]);
        $this->getQuestionTagGroupDao()->update($params['groupId'], ['tagNum' => $this->getQuestionTagDao()->count(['groupId' => $params['groupId']])]);
        $this->getLogService()->info('question_tag', 'add_tag', "添加题目标签：{$params['name']}");
    }

    public function updateTag($id, $params)
    {
        $tag = $this->getQuestionTagDao()->get($id);
        if (empty($tag)) {
            $this->createNewException(QuestionTagException::TAG_NOT_FOUND());
        }
        $params = ArrayToolkit::parts($params, ['name', 'status']);
        if (!empty($params['name'])) {
            $tag = $this->getTagByGroupIdAndName($tag['groupId'], $params['name']);
            if (!empty($tag) && $tag['id'] != $id) {
                $this->createNewException(QuestionTagException::TAG_NAME_DUPLICATE());
            }
        }
        $this->getQuestionTagDao()->update($id, $params);
    }

    public function deleteTag($id)
    {
        $tag = $this->getQuestionTagDao()->get($id);
        if (empty($tag)) {
            return;
        }
        try {
            $this->beginTransaction();
            $this->getQuestionTagDao()->delete($id);
            $tags = $this->searchTags(['groupId' => $tag['groupId']]);
            if (!empty($tags)) {
                $this->sortTags($tag['groupId'], array_column($tags, 'id'));
            }
            $this->getQuestionTagRelationDao()->batchDelete(['tagId' => $id]);
            $this->getQuestionTagGroupDao()->update($tag['groupId'], ['tagNum' => $this->getQuestionTagDao()->count(['groupId' => $tag['groupId']])]);
            $this->getLogService()->info('question_tag', 'delete_tag', "删除题目标签：{$tag['name']}");
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogger()->error("delete question tag {$tag['name']} error: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function searchTags($conditions, $columns = [])
    {
        return $this->getQuestionTagDao()->search($conditions, ['seq' => 'ASC'], 0, PHP_INT_MAX, $columns);
    }

    public function sortTags($groupId, $ids)
    {
        if (empty($ids)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }
        $tags = $this->searchTags(['groupId' => $groupId]);
        if (!ArrayToolkit::isSameValues($ids, array_column($tags, 'id'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }
        $seqs = [];
        foreach ($ids as $key => $id) {
            $seqs[$id] = ['seq' => $key + 1];
        }
        $this->getQuestionTagDao()->batchUpdate($ids, $seqs);
    }

    public function tagQuestions($itemIds, $tagIds)
    {
        if (empty($itemIds)) {
            return;
        }
        $this->getQuestionTagRelationDao()->batchDelete(['itemIds' => $itemIds]);
        $relations = [];
        foreach ($itemIds as $itemId) {
            foreach ($tagIds as $tagId) {
                $relations[] = [
                    'itemId' => $itemId,
                    'tagId' => $tagId,
                ];
            }
        }
        $this->getQuestionTagRelationDao()->batchCreate($relations);
    }

    public function findTagRelationsByTagIds($tagIds)
    {
        return $this->getQuestionTagRelationDao()->findByTagIds($tagIds);
    }

    public function findTagRelationsByItemIds($itemIds)
    {
        return $this->getQuestionTagRelationDao()->findByItemIds($itemIds);
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return QuestionTagGroupDao
     */
    private function getQuestionTagGroupDao()
    {
        return $this->createDao('QuestionTag:QuestionTagGroupDao');
    }

    /**
     * @return QuestionTagDao
     */
    private function getQuestionTagDao()
    {
        return $this->createDao('QuestionTag:QuestionTagDao');
    }

    /**
     * @return QuestionTagRelationDao
     */
    private function getQuestionTagRelationDao()
    {
        return $this->createDao('QuestionTag:QuestionTagRelationDao');
    }
}
