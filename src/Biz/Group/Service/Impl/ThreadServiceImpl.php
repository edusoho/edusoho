<?php


namespace Biz\Group\Service\Impl;


use Biz\BaseService;
use Biz\Group\Dao\ThreadCollectDao;
use Biz\Group\Service\ThreadService;
use Biz\Thread\Dao\ThreadDao;

class ThreadServiceImpl extends BaseService implements ThreadService
{
    public function getThread($id)
    {
        return $this->getThreadDao()->get($id);
    }

    public function searchThreads($conditions, $orderBy, $start, $limit)
    {

        $orderBys = is_array($orderBy) ? $orderBy : $this->filterSort($orderBy);
        return $this->getThreadDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countThreads($conditions)
    {
        return $this->getThreadDao()->count($conditions);
    }

    public function searchThreadCollects($conditions, $orderBy, $start, $limit)
    {
        return $this->getThreadCollectDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countThreadCollects($conditions)
    {
        return $this->getThreadCollectDao()->count($conditions);
    }

    public function searchPostsThreadIds($conditions, $orderBy, $start, $limit)
    {
        return $this->getThreadPostDao()->searchPostsThreadIds($conditions, $orderBy, $start, $limit);
    }

    public function countPostsThreadIds($conditions)
    {
        return $this->getThreadPostDao()->countPostsThreadIds($conditions);
    }

    protected function filterSort($sort)
    {
        switch ($sort) {
            case 'byPostNum':
                $orderBys = array('isStick' => 'DESC', 'postNum' => 'DESC', 'createdTime' => 'DESC');
                break;
            case 'byStick':
            case 'byCreatedTime':
                $orderBys = array('isStick' => 'DESC', 'createdTime' => 'DESC');
                break;
            case 'byLastPostTime':
                $orderBys = array('isStick' => 'DESC', 'lastPostTime' => 'DESC');
                break;
            case 'byCreatedTimeOnly':
                $orderBys = array('createdTime' => 'DESC');
                break;
            default:
                throw $this->createNotFoundException('参数sort不正确。');
        }

        return $orderBys;
    }

    /**
     * @return ThreadDao
     */
    protected function getThreadDao()
    {
        return $this->createDao('Group:ThreadDao');
    }

    /**
     * @return ThreadCollectDao
     */
    protected function getThreadCollectDao()
    {
        return $this->createDao('Group:ThreadCollectDao');
    }

    protected function getThreadPostDao()
    {
        return $this->createDao('Group:ThreadPostDao');
    }
}