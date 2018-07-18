<?php

namespace ApiBundle\Api\Resource\Testpaper;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Testpaper extends AbstractResource
{
    public function get(ApiRequest $request, $testId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw new AccessDeniedHttpException('用户未登录，不能查看试卷');
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        if (empty($testpaper)) {
            throw new NotFoundHttpException('试卷已删除，请联系管理员。!');
        }

        $items = $this->getTestpaperService()->showTestpaperItems($testId);
        $testpaper['metas']['question_type_seq'] = array_keys($items);

        return array(
            'testpaper' => $testpaper,
            'items' => $this->filterTestpaperItems($items),
        );
    }

    private function filterTestpaperItems($items)
    {
        $itemArray = array();

        foreach ($items as $questionType => $item) {
            $itemArray[$questionType] = count($item);
        }

        return $itemArray;
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }
}
