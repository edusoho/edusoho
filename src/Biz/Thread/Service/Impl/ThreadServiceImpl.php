<?php


namespace Biz\Thread\Service\Impl;


use Biz\BaseService;
use Biz\Thread\Dao\ThreadDao;
use Biz\Thread\Service\ThreadService;

class ThreadServiceImpl extends BaseService implements ThreadService
{
    public function searchThreads($conditions, $sort, $start, $limit)
    {
        $orderBys   = $this->filterSort($sort);
        $conditions = $this->prepareThreadSearchConditions($conditions);

        return $this->getThreadDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countThreads($conditions)
    {
        $conditions = $this->prepareThreadSearchConditions($conditions);

        return $this->getThreadDao()->count($conditions);
    }

    protected function filterSort($sort)
    {
        if (is_array($sort)) {
            return $sort;
        }

        switch ($sort) {
            case 'created':
                $orderBys = array('sticky' => 'DESC', 'createdTime' => 'DESC');
                break;
            case 'posted':
                $orderBys = array('sticky' => 'DESC', 'lastPostTime' => 'DESC');
                break;
            case 'createdNotStick':
                $orderBys = array('createdTime' => 'DESC');
                break;
            case 'postedNotStick':
                $orderBys = array('lastPostTime' => 'DESC');
                break;
            case 'popular':
                $orderBys = array('hitNum' => 'DESC');
                break;

            default:
                throw $this->createServiceException($this->getKernel()->trans('参数sort不正确。'));
        }

        return $orderBys;
    }

    protected function prepareThreadSearchConditions($conditions)
    {

        if (empty($conditions['keyword'])) {
            unset($conditions['keyword']);
            unset($conditions['keywordType']);
        }

        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            if (!in_array($conditions['keywordType'], array('title', 'content', 'targetId', 'targetTitle'))) {
                throw $this->createServiceException($this->getKernel()->trans('keywordType参数不正确'));
            }

            $conditions[$conditions['keywordType']] = $conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if (isset($conditions['author'])) {
            $author               = $this->getUserService()->getUserByNickname($conditions['author']);
            $conditions['userId'] = $author ? $author['id'] : -1;
        }

        if (!empty($conditions['latest'])) {
            if ($conditions['latest'] == 'week') {
                $conditions['GTEcreatedTime'] = mktime(0, 0, 0, date('m'), date('d') - 7, date('Y'));
            }
        }

        return $conditions;
    }

    /**
     * @return ThreadDao
     */
    protected function getThreadDao()
    {
        return $this->createDao('Thread:ThreadDao');
    }

}