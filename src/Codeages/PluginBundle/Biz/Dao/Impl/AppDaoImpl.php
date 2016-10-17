<?php
namespace Codeages\PluginBundle\Biz\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\PluginBundle\Biz\Dao\AppDao;


class AppDaoImpl extends GeneralDaoImpl implements AppDao
{
    protected $table = 'app';

    public function declares()
    {
        return array(
            'timestamps' => array('created', 'updated'),
            'serializes' => array('ids1' => 'json', 'ids2' => 'delimiter'),
            'conditions' => array(
                'name = :name',
            ),
        );
    }
}