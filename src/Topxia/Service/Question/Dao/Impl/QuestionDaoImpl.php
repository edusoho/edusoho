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

    public function findQuestionsCountByParentId($parentId)
    {
        
    }

    private function _createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['targetPrefix'])) {
            $conditions['targetLike'] = "{$conditions['targetPrefix']}%";
            unset($conditions['target']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'questions')
            ->andWhere('parentId = :parentId')
            ->andWhere('type = :type')
            ->andWhere('target = :target')
            ->andWhere('target LIKE :targetLike');

        return $builder;
    }

}