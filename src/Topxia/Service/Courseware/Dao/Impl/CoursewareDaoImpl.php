<?php 
namespace Topxia\Service\Courseware\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Courseware\Dao\CoursewareDao;

class CoursewareDaoImpl extends BaseDao implements CoursewareDao
{
    protected $table = "courseware";

    protected $serializeFields = array(
        'relatedKnowledgeIds' => 'json',
        'tagIds' => 'json',
    );

    public function getCourseware($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $courseware = $this->getConnection()->fetchAssoc($sql,array($id)) ? : null;
        return $courseware ? $this->createSerializer()->unserialize($courseware, $this->serializeFields) : null;
    }

    public function searchCoursewares($conditions, $orderBys, $start, $limit)
    {

    }

    public function searchCoursewaresCount($conditions)
    {

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
}