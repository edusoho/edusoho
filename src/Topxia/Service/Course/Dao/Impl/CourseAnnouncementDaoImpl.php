<?php
namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseAnnouncementDao;
use PDO;

class CourseAnnouncementDaoImpl extends BaseDao implements CourseAnnouncementDao
{
    protected $table = 'course_announcement';

    public function getAnnouncement($id)
    {
    	return $this->fetch($id);
    }

    public function findAnnouncementsByCourseId($courseId, $start, $limit)
    {
        return $this->createQueryBuilder()
            ->select('*')->from($this->table, 'course_announcement')
            ->where("courseId = :courseId")
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy('createdTime', 'ASC')
            ->setParameter(":courseId", $courseId)
            ->execute()
            ->fetchAll();
    }

	public function addAnnouncement($fields)
	{
	   $id = $this->insert($fields);
       return $this->getAnnouncement($id);
	}

	public function deleteAnnouncement($id)
	{
		return $this->delete($id);
	}

	public function updateAnnouncement($id, $fields)
	{
		return $this->update($id, $fields);
	}
}