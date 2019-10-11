<?php

namespace Biz\Testpaper\Job;

use Biz\Testpaper\Service\TestpaperService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class UpdateTestpaperTotalScoresJob extends AbstractJob
{
    public function execute()
    {
        $testpapers = $this->getTestpaperService()->searchTestpapers(array(), array(), 0, PHP_INT_MAX);

        foreach ($testpapers as $testpaper) {
            if (isset($testpaper['metas']['totalScores'])) {
                continue;
            }

            $testpaper['metas']['totalScores'] = array(
                'single_choice' => 0,
                'choice' => 0,
                'essay' => 0,
                'uncertain_choice' => 0,
                'determine' => 0,
                'fill' => 0,
                'material' => 0,
            );

            $items = $this->getTestpaperService()->findItemsByTestId($testpaper['id']);
            foreach ($items as $item) {
                if ('material' == $item['questionType']) {
                    continue;
                }

                if ($item['parentId']) {
                    $testpaper['metas']['totalScores']['material'] += $item['score'];
                } else {
                    $testpaper['metas']['totalScores'][$item['questionType']] += $item['score'];
                }
            }

            $this->getTestpaperService()->updateTestpaper($testpaper['id'], $testpaper);
        }
    }

    /**
     * @return TestpaperService
     */
    private function getTestpaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }
}
