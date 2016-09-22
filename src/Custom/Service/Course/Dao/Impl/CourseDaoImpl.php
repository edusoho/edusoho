<?php 
namespace Custom\Service\Course\Dao\Impl;

use Topxia\Service\Course\Dao\Impl\CourseDaoImpl as BaseCourseDaoImpl;

class CourseDaoImpl extends BaseCourseDaoImpl
{
    protected $table = 'course';

    protected function _createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['titleLike'] = "%{$conditions['title']}%";
            unset($conditions['title']);
        }

        if (!empty($conditions['tags'])) {
            $tagIds = $conditions['tags'];
            $tags   = '';

            foreach ($tagIds as $tagId) {
                $tags .= "|".$tagId;
            }

            $conditions['tags'] = $tags."|";
        }

        if (isset($conditions['tagId'])) {
            $tagId = (int) $conditions['tagId'];

            if (!empty($tagId)) {
                $conditions['tagsLike'] = "%|{$conditions['tagId']}|%";
            }

            unset($conditions['tagId']);
        }

        if (empty($conditions['status'])) {
            unset($conditions['status']);
        }

        if (empty($conditions['categoryIds'])) {
            unset($conditions['categoryIds']);
        }

        if (isset($conditions['likeOrgCode'])) {
            $conditions['likeOrgCode'] .= "%";
        }

        $builder = $this->createDynamicQueryBuilder($conditions)

            ->from($this->table, 'course')
            ->andWhere('updatedTime >= :updatedTime_GE')
            ->andWhere('status = :status')
            ->andWhere('type = :type')
            ->andWhere('price = :price')
            ->andWhere('price > :price_GT')
            ->andWhere('originPrice > :originPrice_GT')
            ->andWhere('originPrice = :originPrice')
            ->andWhere('coinPrice > :coinPrice_GT')
            ->andWhere('coinPrice = :coinPrice')
            ->andWhere('originCoinPrice > :originCoinPrice_GT')
            ->andWhere('originCoinPrice = :originCoinPrice')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('userId = :userId')
            ->andWhere('recommended = :recommended')
            ->andWhere('tags LIKE :tagsLike')
            ->andWhere('startTime >= :startTimeGreaterThan')
            ->andWhere('startTime < :startTimeLessThan')
            ->andWhere('rating > :ratingGreaterThan')
            ->andWhere('vipLevelId >= :vipLevelIdGreaterThan')
            ->andWhere('vipLevelId = :vipLevelId')
            ->andWhere('createdTime >= :startTime')
            ->andWhere('createdTime <= :endTime')
            ->andWhere('categoryId = :categoryId')
            ->andWhere('smallPicture = :smallPicture')
            ->andWhere('categoryId IN ( :categoryIds )')
            ->andWhere('vipLevelId IN ( :vipLevelIds )')
            ->andWhere('parentId = :parentId')
            ->andWhere('parentId > :parentId_GT')
            ->andWhere('parentId IN ( :parentIds )')
            ->andWhere('id NOT IN ( :excludeIds )')
            ->andWhere('id IN ( :courseIds )')
            ->andWhere('locked = :locked')
            ->andWhere('lessonNum > :lessonNumGT')
            ->andWhere('orgCode = :orgCode')
            ->andWhere('orgCode LIKE :likeOrgCode')
            ->andWhere('orgId IN ( :orgIds )');

        if (isset($conditions['tagIds'])) {
            $tagIds = $conditions['tagIds'];

            foreach ($tagIds as $key => $tagId) {
                $conditions['tagIds_'.$key] = '%|'.$tagId.'|%';
                $builder->andWhere('tags LIKE :tagIds_'.$key);
            }

            unset($conditions['tagIds']);
        }

        if (isset($conditions['types'])) {
            $builder->andWhere('type IN ( :types )');
        }

        return $builder;
    }
}