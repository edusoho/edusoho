<?php 
namespace Custom\Service\Course\Dao\Impl;
use Topxia\Service\Common\BaseDao;
use Custom\Service\Course\Dao\CourseSearchDao;
class CourseSearchDaoImpl extends BaseDao implements CourseSearchDao
{
	 protected $table = 'course';
	public function searchCourses($conditions, $orderBy, $start, $limit)
	{
		$this->filterStartLimit($start, $limit);
		$builder = $this->_createSearchQueryBuilder($conditions)
		    ->select('*')
		    ->orderBy('createdTime', 'ASC')
		    ->setFirstResult($start)
		    ->setMaxResults($limit);
		if ($orderBy[0] == 'recommendedSeq') {
		    $builder->addOrderBy('recommendedTime', 'DESC');
		}
		return $builder->execute()->fetchAll() ? : array(); 
	}
	public function searchCourseCount($conditions)
	{
		$builder = $this->_createSearchQueryBuilder($conditions)
		    ->select('COUNT(id)');
		return $builder->execute()->fetchColumn(0);
	}
	private function _createSearchQueryBuilder($conditions)
	{
		if (isset($conditions['notFree'])) {
		    $conditions['notFree'] = 0;
		}

		$builder = $this->createDynamicQueryBuilder($conditions)
		    ->from($this->table, 'course')
		    ->andWhere('complexity = :complexity')
		    ->andWhere('price >= :minPrice')
		    ->andWhere('price <= :maxPrice')
		    ->andWhere('status = :status');

		if (isset($conditions['categoryIds'])) {
		    $categoryIds = array();
		    foreach ($conditions['categoryIds'] as $categoryId) {
		        if (ctype_digit((string)abs($categoryId))) {
		            $categoryIds[] = $categoryId;
		        }
		    }
		    if ($categoryIds) {
		        $categoryIds = join(',', $categoryIds);
		        $builder->andStaticWhere("categoryId IN ($categoryIds)");
		    }
		}
		if (isset($conditions['columnId'])) {
			if(!empty($conditions['columnId'])){
			$columnId = $conditions['columnId'];
			$builder->andStaticWhere(" columns LIKE '%|$columnId|%'");
		}
		}
		if(!empty($conditions['tagId'])){
			$tagId = $conditions['tagId'];
			    $builder->andStaticWhere(" tags LIKE '%|$tagId|%'");
		}
		return $builder;
	}

    
    


}
