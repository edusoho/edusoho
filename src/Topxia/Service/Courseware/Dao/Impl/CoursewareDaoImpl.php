<?php 
namespace Topxia\Service\Courseware\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Courseware\Dao\CoursewareDao;

class CoursewareDaoImpl extends BaseDao implements CoursewareDao
{
    protected $table = 'courseware';

    protected $serializeFields = array(
        'relatedKnowledgeIds' => 'json',
        'tagIds' => 'json',
        'knowledgeIds' => 'json'
    );

    public function getCourseware($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $courseware = $this->getConnection()->fetchAssoc($sql,array($id)) ? : null;
        return $courseware ? $this->createSerializer()->unserialize($courseware, $this->serializeFields) : null;
    }

    public function searchCoursewares($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchCoursewaresCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }
    
    public function addCourseware($courseware)
    {
        $courseware = $this->createSerializer()->serialize($courseware,$this->serializeFields);
        $affected = $this->getConnection()->insert($this->table,$courseware);

        if ($affected < 0) {
            throw $this->createDaoException('insert Courseware error.');
        }

        return $this->getCourseware($this->getConnection()->lastInsertId());
    }

    public function updateCourseware($id,$courseware)
    {
        $article = $this->createSerializer()->serialize($courseware, $this->serializeFields);
        $affected = $this->getConnection()->update($this->table, $article, array('id' => $id));

        if ($affected < 0) {
            throw $this->createDaoException('update Courseware error.');
        }

        return $this->getCourseware($id);
    }

    public function deleteCourseware($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    private function _createSearchQueryBuilder($conditions)
    {
        if (!empty($conditions['keyword'])) {
            $conditions['titleLike'] = "%{$conditions['keyword']}%";
            unset($conditions['keyword']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'courseware')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('categoryId = :categoryId');

        if (isset($conditions['tagIds'])) {
            if(!empty($conditions['tagIds'])){
                foreach ($conditions['tagIds'] as $key => $tagId) {
                    if (preg_match('/^[0-9]+$/', $tagId)) {
                        $tagId = "\"".$tagId."\"";
                        $builder->andStaticWhere("tagIds LIKE '%{$tagId}%'");
                    }
                }
            }
            unset($conditions['tagIds']);
        }

        if (isset($conditions['knowledgeIds'])) {
            $ors = array();
            if(!empty($conditions['knowledgeIds'])){
                foreach (array_values($conditions['knowledgeIds']) as $i => $knowledgeId) {
                    if (preg_match('/^[0-9]+$/', $knowledgeId)) {
                        $knowledgeId = "\"".$knowledgeId."\"";
                        $ors[] = "knowledgeIds LIKE '%{$knowledgeId}%'";
                    }
                }
                $builder->andWhere(call_user_func_array(array($builder->expr(), 'orX'), $ors), false);
            }

            unset($conditions['knowledgeIds']);
        }

        return $builder;
    }
}