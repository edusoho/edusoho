<?php 
namespace Topxia\Service\ArticleMaterial\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\ArticleMaterial\Dao\ArticleMaterialDao;

class ArticleMaterialDaoImpl extends BaseDao implements ArticleMaterialDao
{
    protected $table = 'article_material';

    protected $serializeFields = array(
        'relatedKnowledgeIds' => 'json',
        'tagIds' => 'json',
        'knowledgeIds' => 'json'
    );

    public function getArticleMaterial($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $ArticleMaterial = $this->getConnection()->fetchAssoc($sql,array($id)) ? : null;
        return $ArticleMaterial ? $this->createSerializer()->unserialize($ArticleMaterial, $this->serializeFields) : null;
    }

    public function searchArticleMaterials($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchArticleMaterialsCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }
    
    public function addArticleMaterial($ArticleMaterial)
    {
        $ArticleMaterial = $this->createSerializer()->serialize($ArticleMaterial,$this->serializeFields);
        $affected = $this->getConnection()->insert($this->table,$ArticleMaterial);

        if ($affected < 0) {
            throw $this->createDaoException('insert ArticleMaterial error.');
        }

        return $this->getArticleMaterial($this->getConnection()->lastInsertId());
    }

    public function updateArticleMaterial($id,$ArticleMaterial)
    {
        $article = $this->createSerializer()->serialize($ArticleMaterial, $this->serializeFields);
        $affected = $this->getConnection()->update($this->table, $article, array('id' => $id));

        if ($affected < 0) {
            throw $this->createDaoException('update ArticleMaterial error.');
        }

        return $this->getArticleMaterial($id);
    }

    public function deleteArticleMaterial($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    private function _createSearchQueryBuilder($conditions)
    {

        if (isset($conditions['title'])) {
            $conditions['titleLike'] = "%{$conditions['title']}%";
            unset($conditions['title']);
        }

        if (isset($conditions['tagIds'])) {
            $tagIds = $conditions['tagIds'];
            $conditions['tagsLike'] = "%";
            if (!empty($tagIds[0])) {
                foreach ($tagIds as $tagId) {
                    $id = "\"".$tagId."\"";
                    $conditions['tagsLike'] .= "{$id},";
                }
            }
            $conditions['tagsLike'] = rtrim(trim($conditions['tagsLike']), ',' );
            $conditions['tagsLike'] .= "%";
            unset($conditions['tagIds']);
        }

        if (isset($conditions['knowledgeId'])) {
            $knowledgeId = $conditions['knowledgeId'];
            $conditions['knowledgesLike'] = "%";
            if (!empty($knowledgeId)) {
                $conditions['knowledgesLike'] .= "\"".$conditions['knowledgeId']."\"";
            }
            $conditions['knowledgesLike'] .= "%";
            unset($conditions['knowledgeIds']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere('type = :type')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('userId = :userId')
            ->andWhere('categoryId = :categoryId')
            ->andWhere('tagIds LIKE :tagsLike')
            ->andWhere('knowledgeIds LIKE :knowledgesLike')
            ->andWhere('createdTime >= :startTime')
            ->andWhere('createdTime <= :endTime');

        return $builder;
    }
}