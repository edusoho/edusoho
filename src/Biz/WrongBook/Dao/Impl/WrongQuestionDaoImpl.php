<?php

namespace Biz\WrongBook\Dao\Impl;

use Biz\WrongBook\Dao\WrongQuestionDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class WrongQuestionDaoImpl extends AdvancedDaoImpl implements WrongQuestionDao
{
    protected $table = 'biz_wrong_question';

    protected $collectTable = 'biz_wrong_question_collect';

    const WRONG_QUESTION_ORDER_BY = ['submit_time'];

    const  WRONG_QUESTION_COLLECT_ORDER_BY = ['wrong_times'];

    public function findWrongQuestionBySceneIds($items)
    {
        $marks = str_repeat('?,', count($items) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE answer_scene_id IN({$marks});";

        return $this->db()->fetchAll($sql, $items);
    }

    public function searchWrongQuestionsWithDistinctUserId($conditions, $orderBys, $start, $limit)
    {
        $itemId = $conditions['item_id'];
        $sceneIds = implode(',', $conditions['answer_scene_ids']);
        $builder = $this->createQueryBuilder([])
            ->select('*')
            ->rightJoin($this->table, "(SELECT MAX(id) as maxId FROM biz_wrong_question WHERE item_id = {$itemId} AND answer_scene_id IN ({$sceneIds}) GROUP BY user_id)", 'w', "{$this->table}.id = w.maxId")
            ->setFirstResult($start)
            ->setMaxResults($limit);

        foreach ($orderBys ?: [] as $field => $direction) {
            $builder->addOrderBy($field, $direction);
        }

        return $builder->execute()->fetchAll() ?: [];
    }

    public function countWrongQuestionsWithDistinctUserId($conditions)
    {
        $itemId = $conditions['item_id'];
        $sceneIds = implode(',', $conditions['answer_scene_ids']);
        $builder = $this->createQueryBuilder([])
            ->select('COUNT(*)')
            ->rightJoin($this->table, "(SELECT MAX(id) as maxId FROM biz_wrong_question WHERE item_id = {$itemId} AND answer_scene_id IN ({$sceneIds}) GROUP BY user_id)", 'w', "{$this->table}.id = w.maxId");

        return $builder->execute()->fetchColumn(0);
    }

    public function findWrongQuestionsByUserIdsAndItemIdAndSceneIds($userIds, $itemId, $sceneIds)
    {
        $userMarks = str_repeat('?,', count($userIds) - 1).'?';
        $sceneIdsMarks = str_repeat('?,', count($sceneIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE item_id = ? AND user_id IN({$userMarks}) AND answer_scene_id IN({$sceneIdsMarks}) ORDER BY submit_time DESC;";

        return $this->db()->fetchAll($sql, array_merge([$itemId], $userIds, $sceneIds));
    }

    public function findWrongQuestionsByUserIdAndItemIdsAndSceneIds($userId, $itemIds, $sceneIds)
    {
        $itemMarks = str_repeat('?,', count($itemIds) - 1).'?';
        $sceneIdsMarks = str_repeat('?,', count($sceneIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND item_id IN({$itemMarks}) AND answer_scene_id IN({$sceneIdsMarks}) ORDER BY submit_time DESC;";

        return $this->db()->fetchAll($sql, array_merge([$userId], $itemIds, $sceneIds));
    }

    public function findWrongQuestionsByUserIdAndSceneIds($userId, $sceneIds)
    {
        $sceneIdsMarks = str_repeat('?,', count($sceneIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND answer_scene_id IN({$sceneIdsMarks});";

        return $this->db()->fetchAll($sql, array_merge([$userId], $sceneIds));
    }

    public function searchWrongQuestionsWithCollect($conditions, $orderBys, $start, $limit, $columns)
    {
        $preBuilder = $this->createQueryBuilder($conditions)
            ->select("max({$this->table}.id) as id")
            ->innerJoin($this->table, $this->collectTable, 'c', "c.id = {$this->table}.collect_id")
            ->addGroupBy("{$this->table}.item_id");

        if (!empty($conditions['pool_id'])) {
            $preBuilder->andWhere('c.pool_id = :pool_id');
        }

        $ids = array_column($preBuilder->execute()->fetchAll(), 'id');
        if (empty($ids)) {
            return [];
        }
        $builder = $this->createQueryBuilder(['wrong_question_ids' => $ids])
            ->select("{$this->table}.*, c.wrong_times as wrong_times")
            ->andWhere("{$this->table}.id IN (:wrong_question_ids)")
            ->leftJoin($this->table, $this->collectTable, 'c', "c.id = {$this->table}.collect_id")
            ->setFirstResult($start)
            ->setMaxResults($limit);

        foreach ($orderBys ?: [] as $order => $sort) {
            if (in_array($order, self::WRONG_QUESTION_ORDER_BY)) {
                $builder->addOrderBy($this->table.'.'.$order, $sort);
            }
            if (in_array($order, self::WRONG_QUESTION_COLLECT_ORDER_BY)) {
                $builder->addOrderBy('c.'.$order, $sort);
            }
        }

        return $builder->execute()->fetchAll() ?: [];
    }

    public function searchWrongQuestionsWithDistinctItem($conditions, $orderBys, $start, $limit, $columns)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('item_id,COUNT(*) as wrongTimes')
            ->groupBy('item_id')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        if (!empty($orderBys['wrongTimes'])) {
            $builder->addOrderBy('wrongTimes', $orderBys['wrongTimes']);
        }

        return $builder->execute()->fetchAll() ?: [];
    }

    public function countWrongQuestionWithCollect($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->leftJoin($this->table, $this->collectTable, 'c', "c.id = {$this->table}.collect_id")
            ->select("COUNT(DISTINCT {$this->table}.item_id)");
        if (!empty($conditions['pool_id'])) {
            $builder->andWhere('c.pool_id = :pool_id');
        }

        return (int) $builder->execute()->fetchColumn(0);
    }

    public function countWrongQuestionsWithDistinctItem($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('COUNT(DISTINCT(item_id))');

        return (int) $builder->execute()->fetchColumn(0);
    }

    public function declares()
    {
        return [
            'timestamps' => ['created_time', 'updated_time'],
            'conditions' => [
                'id = :id',
                'id IN (:ids)',
                'user_id = :user_id',
                'user_id IN (:user_ids)',
                'item_id = :item_id',
                'item_id IN (:item_ids)',
                'answer_scene_id IN (:answer_scene_ids)',
                'collect_id IN (:collect_ids)',
                'answer_scene_id = :answer_scene_id',
                'testpaper_id = :testpaper_id',
                'testpaper_id IN (:testpaper_ids)',
                'created_time = :created_time',
            ],
            'orderbys' => ['id', 'created_time', 'submit_time'],
        ];
    }
}
