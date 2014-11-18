<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\EduMaterialDao;

class EduMaterialDaoImpl extends BaseDao implements EduMaterialDao
{
    protected $table = 'edu_material';

    public function getEduMaterial($id)
    {
    	$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addEduMaterial($eduMaterial)
    {
    	$affected = $this->getConnection()->insert($this->table, $eduMaterial);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course error.');
        }
        return $this->getEduMaterial($this->getConnection()->lastInsertId());
    }

    public function findAllEduMaterials()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->getConnection()->fetchAll($sql);
    }
}