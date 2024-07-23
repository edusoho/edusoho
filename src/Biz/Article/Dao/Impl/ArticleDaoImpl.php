<?php

namespace Biz\Article\Dao\Impl;

use Biz\Article\Dao\ArticleDao;
use Biz\Common\CommonException;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ArticleDaoImpl extends GeneralDaoImpl implements ArticleDao
{
    protected $table = 'article';

    public function getPrevious($categoryId, $createdTime)
    {
        $sql = "SELECT * FROM {$this->table} WHERE `categoryId` = ? AND createdTime < ? ORDER BY `createdTime` DESC LIMIT 1";

        return $this->db()->fetchAssoc($sql, [$categoryId, $createdTime]) ?: [];
    }

    public function getNext($categoryId, $createdTime)
    {
        $sql = "SELECT * FROM {$this->table} WHERE  `categoryId` = ? AND createdTime > ? ORDER BY `createdTime` ASC LIMIT 1";

        return $this->db()->fetchAssoc($sql, [$categoryId, $createdTime]) ?: [];
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table};";

        return $this->db()->fetchAll($sql, []) ?: [];
    }

    public function findByLikeTitle($title)
    {
        if (empty($title)) {
            return [];
        }

        $sql = "SELECT * FROM {$this->table} WHERE `title` LIKE ?; ";

        return $this->db()->fetchAll($sql, ['%'.$title.'%']);
    }

    public function searchByCategoryIds(array $categoryIds, $start, $limit)
    {
        return $this->search(
            [
                'categoryIds' => $categoryIds,
            ],
            ['createdTime' => 'DESC'],
            $start,
            $limit
        );
    }

    public function countByCategoryIds(array $categoryIds)
    {
        if (empty($categoryIds)) {
            throw CommonException::ERROR_PARAMETER();
        }

        return $this->count(['categoryIds' => $categoryIds]);
    }

    public function waveArticle($id, $field, $diff)
    {
        $fields = ['hits', 'upsNum', 'postNum'];

        if (!in_array($field, $fields)) {
            throw new \InvalidArgumentException(sprintf('%s字段不允许增减，只有%s才被允许增减', $field, implode(',', $fields)));
        }

        return $this->wave([$id], [
            $field => $diff,
        ]);
    }

    protected function createQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);

        if (array_key_exists('property', $conditions)) {
            $key = $conditions['property'];
            $conditions[$key] = 1;
        }

        if (array_key_exists('hasPicture', $conditions)) {
            if ($conditions['hasPicture']) {
                $conditions['pictureNull'] = '';
                unset($conditions['hasPicture']);
            }
        }

        if (array_key_exists('hasThumb', $conditions)) {
            $conditions['thumbNotEqual'] = '';
            unset($conditions['hasThumb']);
        }

        if (isset($conditions['keywords'])) {
            $conditions['keywords'] = "%{$conditions['keywords']}%";
        }

        if (isset($conditions['likeOrgCode'])) {
            $conditions['likeOrgCode'] = $conditions['likeOrgCode'].'%';
            unset($conditions['orgCode']);
        }

        return parent::createQueryBuilder($conditions);
    }

    public function declares()
    {
        return [
            'orderbys' => [
                'createdTime',
                'publishedTime',
                'sticky',
                'hits',
                'updatedTime',
                'featured',
                'promoted',
            ],
            'serializes' => [
                'tagIds' => 'delimiter',
            ],
            'timestamps' => ['createdTime', 'updatedTime'],
            'conditions' => [
                'status = :status',
                'id IN (:articleIds)',
                'categoryId = :categoryId',
                'featured = :featured',
                'promoted = :promoted',
                'sticky = :sticky',
                'title LIKE :keywords',
                'picture != :pictureNull',
                'updatedTime >= :updatedTime_GE',
                'categoryId IN (:categoryIds)',
                'orgCode PRE_LIKE :likeOrgCode',
                'id != :idNotEqual',
                'id = :articleId',
                'thumb != :thumbNotEqual',
                'orgCode = :orgCode',
            ],
        ];
    }
}
