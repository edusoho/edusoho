<?php
namespace Topxia\Service\Question\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Question\Dao\QuestionDao;

class QuestionDaoImpl extends BaseDao implements QuestionDao
{
    protected $table = 'question';

    private $serializeFields = array(
        'answer' => 'json',
        'metas'  => 'json'
    );

    public function getQuestion($id)
    {
        $sql      = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $question = $this->getConnection()->fetchAssoc($sql, array($id));
        return $question ? $this->createSerializer()->unserialize($question, $this->serializeFields) : null;
    }

    public function findQuestionsByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks     = str_repeat('?,', count($ids) - 1).'?';
        $sql       = "SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        $questions = $this->getConnection()->fetchAll($sql, $ids);
        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    public function findQuestionsByCopyIds(array $copyIds)
    {
        if (empty($copyIds)) {
            return array();
        }

        $marks     = str_repeat('?,', count($copyIds) - 1).'?';
        $sql       = "SELECT * FROM {$this->table} WHERE copyId IN ({$marks});";
        $questions = $this->getConnection()->fetchAll($sql, $copyIds);
        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    public function findQuestionsByParentId($id)
    {
        $sql       = "SELECT * FROM {$this->table} WHERE parentId = ? ORDER BY createdTime ASC";
        $questions = $this->getConnection()->fetchAll($sql, array($id));
        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    public function findQuestionsByCopyIdAndLockedTarget($copyId, array $lockedTargets)
    {
        if(empty($lockedTargets)) {
            return array();
        }

        $marks     = str_repeat('?,', count($lockedTargets) - 1).'?';

        $sql = "SELECT * FROM {$this->table} WHERE copyId = ? AND target IN ({$marks})";
        return $this->getConnection()->fetchAll($sql, array_merge(array($copyId), $lockedTargets));
    }

    //@todo:sql 未用到
    public function findQuestionsbyTypes(array $types, $start, $limit)
    {
        if (empty($types)) {
            return array();
        }

        $this->filterStartLimit($start, $limit);

        $marks     = str_repeat('?,', count($types) - 1).'?';

        $sql       = "SELECT * FROM {$this->table} WHERE `parentId` = 0 AND type in ({$marks})  LIMIT {$start},{$limit}";
        $questions = $this->getConnection()->fetchAll($sql, $types);
        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    //@todo:sql 未用到
    public function findQuestionsByTypesAndExcludeUnvalidatedMaterial(array $types, $start, $limit)
    {
        if (empty($types)) {
            return array();
        }

        $this->filterStartLimit($start, $limit);
        $marks     = str_repeat('?,', count($types) - 1).'?';

        $sql       = "SELECT * FROM {$this->table} WHERE (`parentId` = 0) AND (`type` in ({$marks})) and ( not( `type` = 'material' and `subCount` = 0 )) LIMIT {$start},{$limit} ";
        $questions = $this->getConnection()->fetchAll($sql, $types);
        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    //todo: fix
    public function findQuestionsByTypesAndSourceAndExcludeUnvalidatedMaterial($types, $start, $limit, $questionSource, $courseId, $lessonId)
    {
        if (empty($types)) {
            return array();
        }

        if ($questionSource == 'course') {
            $target = 'course-'.$courseId;
        } elseif ($questionSource == 'lesson') {
            $target = 'course-'.$courseId.'/lesson-'.$lessonId;
        }

        $this->filterStartLimit($start, $limit);

        $sql = "SELECT * FROM {$this->table} WHERE (`parentId` = 0) and  (`type` in ($types)) and ( not( `type` = 'material' and `subCount` = 0 )) and (`target` like ? OR `target` = ?) LIMIT {$start},{$limit} ";

        $questions = $this->getConnection()->fetchAll($sql, array("{$target}/%", "{$target}"));
        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    //@todo:sql 未用到
    public function findQuestionsCountbyTypes(array $types)
    {
        if (empty($types)) {
            return 0;
        }

        $marks     = str_repeat('?,', count($types) - 1).'?';

        $sql = "SELECT count(*) FROM {$this->table} WHERE type in ({$marks})";
        return $this->getConnection()->fetchColumn($sql, $types);
    }


    //todo: fix
    public function findQuestionsCountbyTypesAndSource($types, $questionSource, $courseId, $lessonId)
    {
        if ($questionSource == 'course') {
            $target = 'course-'.$courseId;
        } elseif ($questionSource == 'lesson') {
            $target = 'course-'.$courseId.'/lesson-'.$lessonId;
        }

        $sql = "SELECT count(*) FROM {$this->table} WHERE  (`parentId` = 0) and (`type` in ({$types})) and (`target` like ? OR `target` = ?)";
        return $this->getConnection()->fetchColumn($sql, array("{$target}/%", "{$target}"));
    }

    public function findQuestionsByParentIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks     = str_repeat('?,', count($ids) - 1).'?';
        $sql       = "SELECT * FROM {$this->table} WHERE parentId IN ({$marks});";
        $questions = $this->getConnection()->fetchAll($sql, $ids);
        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    public function searchQuestions($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $this->checkOrderBy($orderBy, array('createdTime'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy($orderBy[0], $orderBy[1]);
        $questions = $builder->execute()->fetchAll() ?: array();
        
        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    public function searchQuestionsCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function findQuestionsCountByParentId($parentId)
    {
        $sql = "SELECT count(*) FROM {$this->table} WHERE parentId = ?";
        return $this->getConnection()->fetchColumn($sql, array($parentId));
    }

    public function findQuestionsByTarget($target)
    {
        $sql = "SELECT * FROM {$this->table} WHERE target = ?";
        $questions = $this->getConnection()->fetchAll($sql, array($target));
        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    public function addQuestion($fields)
    {
        $fields   = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert question error.');
        }

        return $this->getQuestion($this->getConnection()->lastInsertId());
    }

    public function updateQuestion($id, $fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getQuestion($id);
    }

    public function deleteQuestion($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteQuestionsByParentId($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE parentId = ?";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    public function updateQuestionCountByIds($ids, $status)
    {
        if (empty($ids)) {
            return array();
        }

        $fields = array('finishedTimes', 'passedTimes');

        if (!in_array($status, $fields)) {
            throw \InvalidArgumentException(sprintf($this->getKernel()->trans("%status%字段不允许增减，只有%fields%才被允许增减",array('%status%'=>$status,'%fields%'=>implode(',', $fields)))));
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "UPDATE {$this->table} SET {$status} = {$status}+1 WHERE id IN ({$marks})";
        return $this->getConnection()->executeQuery($sql, $ids);
    }

    //@todo:sql
    public function getQuestionCountGroupByTypes($conditions)
    {
        $sqlConditions = array();
        $sql           = "";

        if (isset($conditions["types"])) {
            $marks = str_repeat('?,', count($conditions["types"]) - 1).'?';
            $sql .= " and type IN ({$marks}) ";
            $sqlConditions = array_merge($sqlConditions, $conditions["types"]);
        }

        if (isset($conditions["targets"])) {
            $targetMarks   = str_repeat('?,', count($conditions["targets"]) - 1).'?';
            $sqlConditions = array_merge($sqlConditions, $conditions["targets"]);
            $sql .= " and target IN ({$targetMarks}) ";
        }

        if (isset($conditions["courseId"])) {
            $sql .= " and (target=? OR target like ?) ";
            $sqlConditions[] = "course-{$conditions['courseId']}";
            $sqlConditions[] = "course-{$conditions['courseId']}/%";
        }

        $sql = "SELECT COUNT(*) AS questionNum, type FROM {$this->table} WHERE parentId = '0' {$sql} GROUP BY type ";
        return $this->getConnection()->fetchAll($sql, $sqlConditions);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value === '' || is_null($value)) {
                return false;
            }

            return true;
        }

        );

        if (isset($conditions['targetPrefix'])) {
            $conditions['targetLike'] = "{$conditions['targetPrefix']}/%";
            unset($conditions['target']);
        }

        if (isset($conditions['stem'])) {
            $conditions['stem'] = "%{$conditions['stem']}%";
        }

        if (isset($conditions['targets']) && is_array($conditions['targets'])) {
            unset($conditions['target']);
            unset($conditions['targetPrefix']);
        }

        if (isset($conditions['type']) && $conditions['type'] == '0') {
            unset($conditions['type']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'questions')
            ->andWhere("target IN ( :targets )")
            ->andWhere('target = :target')
            ->andWhere('target = :targetPrefix OR target LIKE :targetLike')
            ->andWhere('parentId = :parentId')
            ->andWhere('difficulty = :difficulty')
            ->andWhere('type = :type')
            ->andWhere('stem LIKE :stem')
            ->andWhere("type IN ( :types )")
            ->andwhere("subCount <> :subCount")
            ->andWhere("id NOT IN ( :excludeIds ) ")
            ->andWhere('copyId = :copyId');

        if (isset($conditions['excludeUnvalidatedMaterial']) && ($conditions['excludeUnvalidatedMaterial'] == 1)) {
            $builder->andStaticWhere(" not( type = 'material' AND subCount = 0 )");
        }

        return $builder;
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
