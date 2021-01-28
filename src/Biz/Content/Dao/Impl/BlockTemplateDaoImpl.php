<?php

namespace Biz\Content\Dao\Impl;

use Biz\Content\Dao\BlockTemplateDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class BlockTemplateDaoImpl extends GeneralDaoImpl implements BlockTemplateDao
{
    protected $table = 'block_template';

    public function declares()
    {
        return array(
            'serializes' => array(
                'meta' => 'json',
                'data' => 'json',
            ),
            'conditions' => array(
                'id = :id',
                'category = :category',
                'code IN ( :codes )',
                'title LIKE :title',
            ),
            'orderbys' => array(
                'updateTime', 'createdTime',
            ),
            'timestamps' => array('createdTime', 'updateTime'),
        );
    }

    public function getByCode($code)
    {
        return $this->getByFields(array(
            'code' => $code,
        ));
    }

    protected function createQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['title'] = "%{$conditions['title']}%";
        }

        return parent::createQueryBuilder($conditions);
    }
}
