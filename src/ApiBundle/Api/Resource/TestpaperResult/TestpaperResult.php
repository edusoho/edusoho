<?php

namespace ApiBundle\Api\Resource\TestpaperResult;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Testpaper\Service\TestpaperService;

class TestpaperResult extends AbstractResource
{
    public function add(ApiRequest $request, $resultId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (!empty($testpaperResult) && !in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return true;
        }

        if ($user['id'] != $testpaperResult['userId']) {
            return false;
        }

        $data = $request->request->all();

        $testpaperResult = $this->getTestpaperService()->finishTest($testpaperResult['id'], $data);

        return $testpaperResult;
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->service('Testpaper:TestpaperService');
    }
}
