<?php

namespace Tests\Unit\GoodsMarketing\Dao;

use Biz\GoodsMarketing\Dao\MarketingMeansDao;
use Tests\Unit\Base\BaseDaoTestCase;

class MarketingMeansDaoTest extends BaseDaoTestCase
{
    public function getDefaultMockFields()
    {
        return [
            'type' => 'discount',
            'fromMeansId' => 1,
            'targetType' => 'goods',
            'targetId' => 1,
            'status' => 1,
            'visibleOnGoodsPage' => 1,
        ];
    }

    public function testFindValidMeansByTargetTypeAndTargetId()
    {
        $expected = $this->mockDataObject();
        $results = $this->getDao()->findValidMeansByTargetTypeAndTargetId('goods', 1);

        $this->assertCount(1, $results);
        $this->assertEquals($expected, reset($results));
    }

    /**
     * @return MarketingMeansDao
     */
    protected function getMarketingMeans()
    {
        return $this->createDao('GoodsMarketing:MarketingMeansDao');
    }
}
