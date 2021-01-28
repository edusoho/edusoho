<?php

namespace Biz\Content\Dao\Impl;

use Biz\Content\Dao\ContentDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ContentDaoImpl extends GeneralDaoImpl implements ContentDao
{
    protected $table = 'content';

    public function declares()
    {
        return array(
            'conditions' => array(
                'type = :type',
                'status = :status',
                'title LIKE :keywords',
                'categoryId IN (:categoryIds)',
            ),
            'orderbys' => array(
                'id',
                'createdTime',
            ),
        );
    }

    public function getByAlias($alias)
    {
        return $this->getByFields(array(
            'alias' => $alias,
        ));
    }

    protected function createQueryBuilder($conditions)
    {
        if (isset($conditions['keywords'])) {
            $conditions['keywords'] = "%{$conditions['keywords']}%";
        }

        return parent::createQueryBuilder($conditions);
    }
}
