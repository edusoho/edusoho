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
        if (!empty($conditions['keyword'])) {
            $conditions['titleLike'] = "%{$conditions['keyword']}%";
            unset($conditions['keyword']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'article_material')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('categoryId = :categoryId');

        if (isset($conditions['tagIds'])) {
            $tagIds = $conditions['tagIds'];
            if(!empty($tagIds)){
                foreach ($tagIds as $key => $tagId) {
                    if (preg_match('/^[0-9]+$/', $tagId)) {
                        $builder->andStaticWhere("tagIds LIKE '%|{$tagId}|%'");
                    }
                }
            }
            unset($conditions['tagIds']);
        }

        if (isset($conditions['knowledgeIds'])) {
            $knowledgeIds = $conditions['knowledgeIds'];
            $ors = array();
            if(!empty($knowledgeIds)){
                foreach (array_values($knowledgeIds) as $i => $knowledgeId) {
                    if (preg_match('/^[0-9]+$/', $knowledgeId)) {
                        $ors[] = "knowledgeIds LIKE '%|{$knowledgeId}|%'";
                    }
                }
                $builder->andWhere(call_user_func_array(array($builder->expr(), 'orX'), $ors), false);
            }

            unset($conditions['knowledgeIds']);
        }

        return $builder;
    }
}