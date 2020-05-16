<?php

namespace AppBundle\Worker;

use Biz\Course\Service\CourseService;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Codeages\Plumber\Queue\Job;

class UpdateProductWorker extends BaseWorker
{
    public function doExecute(Job $job)
    {
        $body = json_decode($job->getBody(), true);

        $course = $this->getCourseByProductId($body['messages']['productId']);

        if (empty($course)) {
            $this->logger->info("{$body['worker']}:Update product version filed: Course not found.");

            return true;
        }

        $merchant = $merchant = $this->getS2B2CFacadeService()->getMe();

        if (empty($merchant['status']) || 'active' != $merchant['status'] || 'cooperation' != $merchant['coop_status']) {
            $this->logger->info("{$body['worker']}:Update product version filed: Merchant status invalid.");

            return true;
        }

        $result = $this->getS2B2CCourseProductService()->updateCourseVersionData($course['id']);

        $this->logger->info("{$body['worker']}:Update product version result: ".json_encode($result));

        return true;
    }

    protected function getCourseByProductId($productId)
    {
        $product = $this->getS2B2CProductService()->getProduct($productId);

        return $this->getCourseService()->getCourse($product['localResourceId']);
    }

    /**
     * @return ProductService
     */
    protected function getS2B2CProductService()
    {
        return $this->getBiz()->service('S2B2C:ProductService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->getBiz()->service('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return CourseProductService
     */
    protected function getS2B2CCourseProductService()
    {
        return $this->getBiz()->service('S2B2C:CourseProductService');
    }
}
