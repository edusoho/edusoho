<?php

namespace Biz\WrongBook\Dao\Impl;

use Biz\WrongBook\Dao\WrongQuestionDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class WrongQuestionDaoImpl extends AdvancedDaoImpl implements WrongQuestionDao
{
    protected $table = 'biz_wrong_question';

    protected $collectTable = 'biz_wrong_question_collect';

    const WRONG_QUESTION_ORDER_BY = ['submit_time', 'has_answer'];

    const WRONG_QUESTION_COLLECT_ORDER_BY = ['wrong_times', 'last_submit_time'];

    public function findWrongQuestionBySceneIds($sceneIds)
    {
        return $this->findInField('answer_scene_id', array_values($sceneIds));
    }

    public function findWrongQuestionByCollectIds($collectIds)
    {
        return $this->findInField('collect_id', array_values($collectIds));
    }

    public function searchWrongQuestionsWithDistinctUserId($conditions, $orderBys, $start, $limit)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('max(id) as id,user_id,max(submit_time) as submit_time,max(answer_question_report_id) as answer_question_report_id,COUNT(*) as wrongTimes')
            ->groupBy('user_id')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $builder->addOrderBy('id', 'DESC');

        return $builder->execute()->fetchAll() ?: [];
    }

    public function countWrongQuestionsWithDistinctUserId($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('COUNT(DISTINCT user_id)');

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

        if (!empty($conditions['pool_ids'])) {
            $preBuilder->andWhere('c.pool_id IN (:pool_ids)');
        }

        if (!empty($conditions['status'])) {
            $preBuilder->andWhere('c.status = :status');
        }

        $ids = array_column($preBuilder->execute()->fetchAll(), 'id');
        if (empty($ids)) {
            return [];
        }
        $builder = $this->createQueryBuilder(['wrong_question_ids' => $ids])
            ->select("{$this->table}.*, c.wrong_times as wrong_times, c.last_submit_time as last_submit_time, c.item_id as item_id")
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

        $builder->addOrderBy($this->table.'.id', 'DESC');

        return $builder->execute()->fetchAll() ?: [];
    }

    public function searchWrongQuestionsWithDistinctItem($conditions, $orderBys, $start, $limit, $columns)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('max(id) as id,item_id, max(collect_id) as collect_id,COUNT(*) as wrongTimes, max(answer_scene_id) as answer_scene_id')
            ->groupBy('item_id')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        if (!empty($orderBys['wrongTimes'])) {
            $builder->addOrderBy('wrongTimes', $orderBys['wrongTimes']);
        }

        $builder->addOrderBy('id', 'DESC');

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

        if (!empty($conditions['status'])) {
            $builder->andWhere('c.status = :status');
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
                'collect_id = :collect_id',
                'collect_id IN (:collect_ids)',
                'answer_scene_id = :answer_scene_id',
                'testpaper_id = :testpaper_id',
                'testpaper_id IN (:testpaper_ids)',
                'created_time = :created_time',
            ],
            'orderbys' => ['id', 'created_time', 'submit_time', 'has_answer'],
        ];
    }
}
