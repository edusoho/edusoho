<?php

namespace Biz\OpenCourse\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Goods\Service\GoodsService;
use Biz\OpenCourse\Dao\OpenCourseRecommendedDao;
use Biz\OpenCourse\Service\OpenCourseRecommendedService;

class OpenCourseRecommendedServiceImpl extends BaseService implements OpenCourseRecommendedService
{
    public function getRecommendedCourseByCourseIdAndType($openCourseId, $recommendCourseId, $type)
    {
        return $this->getOpenCourseRecommendDao()->getByCourseIdAndType($openCourseId, $recommendCourseId, $type);
    }

    public function addRecommendGoods($openCourseId, $goodsIds)
    {
        if (empty($goodsIds)) {
            return;
        }
        $goodses = $this->getGoodsService()->findGoodsByIds($goodsIds);
        $recommendGoodses = [];

        foreach ($goodses as $goods) {
            $existRecommendGoods = $this->getOpenCourseRecommendDao()->getByOpenCourseIdAndGoodsId($openCourseId, $goods['id']);

            if (!$existRecommendGoods) {
                $recommendGoodses[] = $this->getOpenCourseRecommendDao()->create([
                    'recommendGoodsId' => $goods['id'],
                    'openCourseId' => $openCourseId,
                    'type' => $goods['type'],
                ]);
            }
        }
        $this->refreshSeq($openCourseId, ArrayToolkit::column($recommendGoodses, 'id'));

        return $recommendGoodses;
    }

    public function addRecommendedCourses($openCourseId, $recommendCourseIds, $type)
    {
        if (empty($recommendCourseIds)) {
            return true;
        }

        $recommendCourses = [];

        foreach ($recommendCourseIds as $key => $courseId) {
            $exitsRecommendCourse = $this->getRecommendedCourseByCourseIdAndType($openCourseId, $courseId, $type);

            if (!$exitsRecommendCourse) {
                $fields = [
                    'recommendCourseId' => $courseId,
                    'openCourseId' => $openCourseId,
                    'type' => $type,
                ];
                $recommendCourses[] = $this->getOpenCourseRecommendDao()->create($fields);
            }
        }

        $recommendIds = ArrayToolkit::column($recommendCourses, 'id');

        $this->refreshSeq($openCourseId, $recommendIds);

        return $recommendCourses;
    }

    public function updateOpenCourseRecommendedCourses($openCourseId, $activeRecommendIds)
    {
        $allExistingRecommendedCourses = $this->findRecommendedGoodsByOpenCourseId($openCourseId);

        $existRecommendedIds = ArrayToolkit::column($allExistingRecommendedCourses, 'id');

        if (empty($activeRecommendIds)) {
            $this->deleteBatchRecommend($existRecommendedIds);
        } else {
            $diff = array_diff($existRecommendedIds, $activeRecommendIds);

            if (!empty($diff)) {
                $this->deleteBatchRecommend($diff);
            }
        }

        $this->refreshSeq($openCourseId, $activeRecommendIds);
    }

    public function findRecommendedGoodsByOpenCourseId($openCourseId)
    {
        return $this->getOpenCourseRecommendDao()->findByOpenCourseId($openCourseId);
    }

    protected function refreshSeq($openCourseId, $recommendIds)
    {
        $existingRecommended = $this->findRecommendedGoodsByOpenCourseId($openCourseId);
        $existingRecommended = ArrayToolkit::index($existingRecommended, 'id');

        $seq = 1;

        if (empty($recommendIds)) {
            return;
        }

        foreach ($recommendIds as $key => $recommendId) {
            $existing = empty($existingRecommended[$recommendId]) ? [] : $existingRecommended[$recommendId];

            if ($existing) {
                $this->getOpenCourseRecommendDao()->update($existing['id'], ['seq' => $seq]);
                ++$seq;
            }
        }

        return true;
    }

    public function deleteRecommend($recommendId)
    {
        return $this->deleteBatchRecommend([$recommendId]);
    }

    public function deleteBatchRecommend($recommendIds)
    {
        if (empty($recommendIds)) {
            return true;
        }
        $this->getOpenCourseRecommendDao()->batchDelete(['ids' => $recommendIds]);

        return true;
    }

    protected function addRecommendeds($recommendCourseIds, $openCourseId, $type)
    {
        foreach ($recommendCourseIds as $key => $courseId) {
            $recommended = [
                'recommendCourseId' => $courseId,
                'openCourseId' => $openCourseId,
                'type' => $type,
            ];
            $this->getOpenCourseRecommendDao()->create($recommended);
        }

        return true;
    }

    public function countRecommends($conditions)
    {
        return $this->getOpenCourseRecommendDao()->count($conditions);
    }

    public function searchRecommends($conditions, $orderBy, $start, $limit)
    {
        return $this->getOpenCourseRecommendDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function recommendedGoodsSort($recommends)
    {
        $goodsIds = ArrayToolkit::column($recommends, 'recommendGoodsId');
        $goodses = ArrayToolkit::index($this->getGoodsService()->findGoodsByIds($goodsIds), 'id');

        foreach ($recommends as &$recommend) {
            $recommend['goods'] = empty($goodses[$recommend['recommendGoodsId']]) ? [] : $goodses[$recommend['recommendGoodsId']];
        }

        return $recommends;
    }

    public function findRandomRecommendGoods($courseId, $num = 3)
    {
        if ($num < 0) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }
        $recommendCourses = $this->getOpenCourseRecommendDao()->findRandomRecommendCourses($courseId, $num);

        $goodsIds = ArrayToolkit::column($recommendCourses, 'recommendGoodsId');

        return $this->getGoodsService()->findGoodsByIds($goodsIds);
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return OpenCourseRecommendedDao
     */
    protected function getOpenCourseRecommendDao()
    {
        return $this->createDao('OpenCourse:OpenCourseRecommendedDao');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }
}
