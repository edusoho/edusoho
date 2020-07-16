<?php

namespace AppBundle\Command;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GoodsDataInitCommand extends BaseCommand
{
    public function configure()
    {
        /*
         * [
         *  {"userId":"1", "date":"2020-06-24", "learnedTime":"3600"},
         *  ...
         * ]
         */
        $this->setName('goods:data-init')
            ->setDescription('初始化商品数据');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始</info>');
        $this->initServiceKernel();

        $this->syncProductAndGoods();
        $this->syncClassroomProductAndGoods();
        $output->writeln('<info>结束</info>');
    }

    protected function syncProductAndGoods()
    {
        $courseSets = $this->getCourseSetService()->searchCourseSets([
            'parentId' => 0,
        ], ['id' => 'ASC'], 0, PHP_INT_MAX);

        foreach ($courseSets as $courseSet) {
            $product = $this->getProductService()->getProductByTargetIdAndType($courseSet['id'], 'course');
            if (empty($product)) {
                list($product, $goods) = $this->addCourseSetProductAndGoods($courseSet);
            } else {
                list($product, $goods) = $this->syncCourseSetProductsAndGoods($courseSet);
            }
            $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);
            $specses = ArrayToolkit::index($this->getGoodsService()->findGoodsSpecsByGoodsId($goods['id']), 'targetId');
            foreach ($courses as $course) {
                if (empty($specses[$course['id']])) {
                    $this->addCourseGoodsSpecs($product, $goods, $course);
                    unset($specses[$course['id']]);
                } else {
                    $this->syncCourseGoodsSpecs($product, $goods, $course);
                    unset($specses[$course['id']]);
                }
            }
            $deleteGoodsSpecsIds = array_values(ArrayToolkit::column($specses, 'id'));
            foreach ($deleteGoodsSpecsIds as $specsId) {
                $this->getGoodsService()->deleteGoodsSpecs($specsId);
            }
        }

        return 1;
    }

    protected function syncClassroomProductAndGoods()
    {
        $classrooms = $this->getClassroomService()->searchClassrooms([], [], 0, PHP_INT_MAX);

        foreach ($classrooms as $classroom) {
            $product = $this->getProductService()->getProductByTargetIdAndType($classroom['id'], 'classroom');
            if (empty($product)) {
                list($product, $goods, $goodsSpecs) = $this->addClassroomProductAndGoodsAndSpecs($classroom);
            } else {
                list($product, $goods, $goodsSpecs) = $this->syncClassroomProductAndGoodsAndSpecs($classroom);
            }
        }
    }

    protected function syncClassroomProductAndGoodsAndSpecs($classroom)
    {
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($classroom['id'], 'classroom');
        if (empty($existProduct)) {
            throw new \Exception('product not found');
        }

        $product = $this->getProductService()->updateProduct($existProduct['id'], [
            'title' => $classroom['title'],
        ]);

        $existGoods = $this->getGoodsService()->getGoodsByProductId($existProduct['id']);

        if (empty($existGoods)) {
            throw new \Exception('goods not found');
        }

        $goods = $this->getGoodsService()->updateGoods($existGoods['id'], [
            'title' => $classroom['title'],
            'subtitle' => '',
            'summary' => $classroom['about'],
            'images' => [
                'large' => $classroom['largePicture'],
                'middle' => $classroom['largePicture'],
                'small' => $classroom['smallPicture'],
            ],
            'orgId' => $classroom['orgId'],
            'orgCode' => $classroom['orgCode'],
            'status' => 'published' === $classroom['status'] ? 'published' : 'unpublished',
        ]);

        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $classroom['id']);

        $goodsSpecs = $this->getGoodsService()->updateGoodsSpecs($goodsSpecs['id'], [
            'title' => $classroom['title'],
            'images' => $goods['images'],
            'price' => $classroom['price'],
            'buyable' => $classroom['buyable'],
            'showable' => $classroom['showable'],
            'buyableMode' => $classroom['expiryMode'],
            'buyableEndTime' => $classroom['expiryValue'],
            'services' => $classroom['service'],
            'status' => 'published' === $classroom['status'] ? 'published' : 'unpublished',
        ]);

        return [$product, $goods, $goodsSpecs];
    }

    protected function addClassroomProductAndGoodsAndSpecs($classroom)
    {
        $product = $this->getProductService()->createProduct([
            'targetType' => 'classroom',
            'targetId' => $classroom['id'],
            'title' => $classroom['title'],
            'owner' => $classroom['creator'],
        ]);

        $goods = $this->getGoodsService()->createGoods([
            'type' => 'classroom',
            'productId' => $product['id'],
            'title' => $classroom['title'],
            'subtitle' => '',
            'creator' => $classroom['creator'],
            'status' => 'published' === $classroom['status'] ? 'published' : 'unpublished',
        ]);

        $goodsSpecs = $this->getGoodsService()->createGoodsSpecs([
            'goodsId' => $goods['id'],
            'targetId' => $classroom['id'],
            'title' => $classroom['title'],
            'status' => 'published' === $classroom['status'] ? 'published' : 'unpublished',
        ]);

        return [$product, $goods, $goodsSpecs];
    }

    public function syncCourseGoodsSpecs($product, $goods, $course)
    {
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $course['id']);
        $goodsSpecs = $this->getGoodsService()->updateGoodsSpecs($goodsSpecs['id'], [
            'title' => empty($course['title']) ? $course['courseSetTitle'] : $course['title'],
            'images' => $goods['images'],
            'status' => 'published' === $course['status'] ? 'published' : 'unpublished',
            'seq' => $course['seq'],
            'price' => $course['price'],
            'coinPrice' => $course['coinPrice'],
            'buyableMode' => $course['expiryMode'],
            'buyableStartTime' => $course['expiryStartDate'] ?: 0,
            'buyableEndTime' => $course['expiryEndDate'] ?: 0,
            'maxJoinNum' => $course['maxStudentNum'],
            'services' => $course['services'],
        ]);

        return $goodsSpecs;
    }

    public function addCourseGoodsSpecs($product, $goods, $course)
    {
        $goodsSpecs = $this->getGoodsService()->createGoodsSpecs([
            'status' => 'published' === $course['status'] ? 'published' : 'unpublished',
            'goodsId' => $goods['id'],
            'targetId' => $course['id'],
            'title' => empty($course['title']) ? $course['courseSetTitle'] : $course['title'],
            'seq' => $course['seq'],
            'buyableMode' => $course['expiryMode'],
        ]);

        return $goodsSpecs;
    }

    protected function addCourseSetProductAndGoods($courseSet)
    {
        $product = $this->getProductService()->createProduct([
            'targetType' => 'course',
            'targetId' => $courseSet['id'],
            'title' => $courseSet['title'],
            'owner' => $courseSet['creator'],
        ]);

        $goods = $this->getGoodsService()->createGoods([
            'type' => 'course',
            'status' => 'published' === $courseSet['status'] ? 'published' : 'unpublished',
            'productId' => $product['id'],
            'title' => $courseSet['title'],
            'subtitle' => $courseSet['subtitle'],
            'creator' => $courseSet['creator'],
        ]);

        return [$product, $goods];
    }

    protected function syncCourseSetProductsAndGoods($courseSet)
    {
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($courseSet['id'], 'course');
        if (empty($existProduct)) {
            throw new \Exception('product not found');
        }

        $product = $this->getProductService()->updateProduct($existProduct['id'], [
            'title' => $courseSet['title'],
        ]);

        $existGoods = $this->getGoodsService()->getGoodsByProductId($existProduct['id']);

        if (empty($existGoods)) {
            $goods = $this->getGoodsService()->createGoods([
                'type' => 'course',
                'status' => 'published' === $courseSet['status'] ? 'published' : 'unpublished',
                'productId' => $product['id'],
                'title' => $courseSet['title'],
                'subtitle' => $courseSet['subtitle'],
                'creator' => $courseSet['creator'],
            ]);

            return [$product, $goods];
        }

        $goods = $this->getGoodsService()->updateGoods($existGoods['id'], [
            'title' => $courseSet['title'],
            'status' => 'published' === $courseSet['status'] ? 'published' : 'unpublished',
            'subtitle' => $courseSet['subtitle'],
            'summary' => $courseSet['summary'],
            'images' => $courseSet['cover'],
            'orgId' => $courseSet['orgId'],
            'orgCode' => $courseSet['orgCode'],
        ]);

        return [$product, $goods];
    }

    protected function getProductService()
    {
        return $this->getBiz()->service('Product:ProductService');
    }

    /**
     * @return \Biz\Goods\Service\GoodsService
     */
    protected function getGoodsService()
    {
        return $this->getBiz()->service('Goods:GoodsService');
    }

    /**
     * @return \Biz\Course\Service\CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }
}
