<?php


namespace Biz\Course\Service\Impl;


use Biz\BaseService;
use Biz\Course\Service\ThreadService;
use Topxia\Service\Common\ServiceKernel;

class ThreadServiceImpl extends BaseService implements ThreadService
{
    public function countThreads($conditions)
    {
        $conditions = $this->prepareThreadSearchConditions($conditions);

        return $this->getThreadDao()->count($conditions);
    }

    public function searchThreads($conditions, $sort, $start, $limit)
    {
        $orderBys   = $this->filterSort($sort);
        $conditions = $this->prepareThreadSearchConditions($conditions);
        return $this->getThreadDao()->search($conditions, $orderBys, $start, $limit);
    }

    protected function prepareThreadSearchConditions($conditions)
    {
        if (empty($conditions['keyword'])) {
            unset($conditions['keyword']);
            unset($conditions['keywordType']);
        }


        if (isset($conditions['threadType'])) {
            $conditions[$conditions['threadType']] = 1;
            unset($conditions['threadType']);
        }

        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            if (!in_array($conditions['keywordType'], array('title', 'content', 'courseId', 'courseTitle'))) {
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

        return $conditions;
    }

    protected function filterSort($sort)
    {
        if (is_array($sort)) {
            return $sort;
        }

        switch ($sort) {
            case 'created':
                $orderBys = array('isStick' => 'DESC', 'createdTime' => 'DESC');
                break;
            case 'posted':
                $orderBys = array('isStick' => 'DESC', 'latestPostTime' => 'DESC');
                break;
            case 'createdNotStick':
                $orderBys = array('createdTime' => 'DESC');
                break;
            case 'postedNotStick':
                $orderBys = array('latestPostTime' => 'DESC');
                break;
            case 'popular':
                $orderBys = array('hitNum' => 'DESC');
                break;
            default:
                throw $this->createServiceException($this->getKernel()->trans('参数sort不正确。'));
        }

        return $orderBys;
    }

    protected function getThreadDao()
    {
        return $this->createDao('Course:ThreadDao');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }

}