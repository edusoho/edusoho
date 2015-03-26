<?php
namespace Topxia\Service\Question\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Question\Dao\QuestionDao;

class QuestionDaoImpl extends BaseDao implements QuestionDao
{
    protected $table = 'question';

    private $serializeFields = array(
            'answer' => 'json',
            'metas' => 'json',
    );

    public function getQuestion($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $question = $this->getConnection()->fetchAssoc($sql, array($id));
        return $question ? $this->createSerializer()->unserialize($question, $this->serializeFields) : null;
    }

    public function findQuestionsByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        $questions = $this->getConnection()->fetchAll($sql, $ids);
        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    public function findQuestionsByParentId($id)
    {
        $sql ="SELECT * FROM {$this->table} WHERE parentId = ? ORDER BY createdTime ASC";
        $questions = $this->getConnection()->fetchAll($sql, array($id));
        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    //@todo:sql
    public function findQuestionsbyTypes($types, $start, $limit)
    {
        if (empty($types)) {
            return array();
        }

        $sql ="SELECT * FROM {$this->table} WHERE `parentId` = 0 AND type in ({$types})  LIMIT {$start},{$limit}";
        $questions = $this->getConnection()->fetchAll($sql, array($types));
        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    //@todo:sql
    public function findQuestionsByTypesAndExcludeUnvalidatedMaterial($types, $start, $limit)
    {
        if (empty($types)) {
            return array();
        }

        $sql ="SELECT * FROM {$this->table} WHERE (`parentId` = 0) AND (`type` in ({$types})) AND ( not( `type` = 'material' AND `subCount` = 0 )) LIMIT {$start},{$limit} ";
        $questions = $this->getConnection()->fetchAll($sql, array($types));
        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    //@todo:sql
    public function findQuestionsByTypesAndSourceAndExcludeUnvalidatedMaterial($types, $start, $limit, $questionSource, $courseId, $lessonId)
    {
        if (empty($types)) {
            return array();
        }
        if ($questionSource == 'course'){
            $target = 'course-'.$courseId;
        }else if ($questionSource == 'lesson'){
            $target = 'course-'.$courseId.'/lesson-'.$lessonId;
        }
        $sql ="SELECT * FROM {$this->table} WHERE (`parentId` = 0) AND  (`type` in ($types)) AND ( not( `type` = 'material' AND `subCount` = 0 )) AND (`target` like '{$target}/%' OR `target` = '{$target}') LIMIT {$start},{$limit} ";
        
        $questions = $this->getConnection()->fetchAll($sql, array());
        return $this->createSerializer()->unserializes($questions, $this->serializeFields);
    }

    //@todo:sql
    public function findQuestionsCountbyTypes($types)
    {
        $sql ="SELECT count(*) FROM {$this->table} WHERE type in ({$types})";
        return $this->getConnection()->fetchColumn($sql, array($types));
    }

    //@todo:sql
    public function findQuestionsCountbyTypesAndSource($types,$questionSource,$courseId,$lessonId)
    {
        if ($questionSource == 'course'){
            $target = 'course-'.$courseId;
        }else if ($questionSource == 'lesson'){
            $target = 'course-'.$courseId.'/lesson-'.$lessonId;
        }
        $sql ="SELECT count(*) FROM {$this->table} WHERE  (`parentId` = 0) AND (`type` in ({$types})) AND (`target` like '{$target}/%' OR `target` = '{$target}')";
        return $this->getConnection()->fetchColumn($sql, array());
    }

    public function findQuestionsByParentIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE parentId IN ({$marks});";
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

        $questions = $builder->execute()->fetchAll() ? : array();

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
        $sql ="SELECT count(*) FROM {$this->table} WHERE parentId = ?";
        return $this->getConnection()->fetchColumn($sql, array($parentId));
    }

    public function addQuestion($fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
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
        if(empty($ids)){ 
            return array(); 
        }

        $fields = array('finishedTimes', 'passedTimes');
        if(!in_array($status, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $status, implode(',', $fields)));
        }

        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "UPDATE {$this->table} SET {$status} = {$status}+1 WHERE id IN ({$marks})";
        return $this->getConnection()->executeQuery($sql, $ids);
    }

    //@todo:sql
    public function getQuestionCountGroupByTypes($conditions)
    {   
        $sqlConditions = array();
        $sql = "";
        if(isset($conditions["types"])){
            $marks = str_repeat('?,', count($conditions["types"]) - 1) . '?';
            $sql .= " AND type IN ({$marks}) ";
            $sqlConditions = array_merge($sqlConditions, $conditions["types"]);
        }
        if(isset($conditions["targets"])) {
            $targetMarks = str_repeat('?,', count($conditions["targets"]) - 1) . '?';
            $sqlConditions = array_merge($sqlConditions, $conditions["targets"]);
            $sql .= " AND target IN ({$targetMarks}) ";
        }
        if(isset($conditions["courseId"])) {
            $sql .= " AND (target='course-{$conditions['courseId']}' or target like 'course-{$conditions['courseId']}/%') ";   
        }
        $sql = "SELECT COUNT(*) AS questionNum, type FROM {$this->table} WHERE parentId = '0' {$sql} GROUP BY type ";
        return $this->getConnection()->fetchAll($sql, $sqlConditions);
    }

    //@todo:sql
    private function _createSearchQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function($value) {
            if ($value === '' or is_null($value)) {
                return false;
            }
            return true;
        });

        if (isset($conditions['targetPrefix'])) {
            $conditions['targetLike'] = "{$conditions['targetPrefix']}/%";
            unset($conditions['target']);
        }

        if (isset($conditions['stem'])) {
            $conditions['stem'] = "%{$conditions['stem']}%";
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'questions');

        if (isset($conditions['targets']) and is_array($conditions['targets'])) {
            $targets = array();
            foreach ($conditions['targets'] as $target) {
                if (empty($target)) {
                    continue;
                }
                if (preg_match('/^[a-zA-Z0-9_\-\/]+$/', $target)) {
                    $targets[] = $target;
                }
            }
            if (!empty($targets)) {
                $targets = "'" . implode("','", $targets) . "'";
                $builder->andStaticWhere("target IN ({$targets})");
            }
        } else {
            $builder->andWhere('target = :target')
                ->andWhere('target = :targetPrefix OR target LIKE :targetLike');
        }

        $builder->andWhere('parentId = :parentId')
            ->andWhere('difficulty = :difficulty')
            ->andWhere('type = :type')
            ->andWhere('stem LIKE :stem');

        if (isset($conditions['types'])) {  
            $types = array();
            foreach ($conditions['types'] as $type) {
                if (empty($type)) {
                    continue;
                }
                if (preg_match('/^[a-zA-Z0-9_\-\/]+$/', $type)) {
                    $types[] = $type;
                }
            }
            if (!empty($types)) {
                $types = "'" . implode("','", $types) . "'";
                $builder->andStaticWhere("type IN ({$types})");
            }
        }

        if (isset($conditions['excludeIds']) and is_array($conditions['excludeIds'])) {
            $excludeIds = array();
            foreach ($conditions['excludeIds'] as $id) {
                $excludeIds[] = intval($id);
            }

            if (!empty($excludeIds)) {
                $builder->andStaticWhere("id NOT IN (" . implode(',', $excludeIds) . ")");
            }
        }

        if (isset($conditions['excludeUnvalidatedMaterial']) and ($conditions['excludeUnvalidatedMaterial'] == 1)){
            $builder->andStaticWhere(" not( type = 'material' and subCount = 0 )");
        }
        return $builder;
    }

}