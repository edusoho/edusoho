<?php

namespace Tests\Unit\Favorite;

use Biz\BaseTestCase;
use Biz\Favorite\Dao\FavoriteDao;

class FavoriteDaoTest extends BaseTestCase
{
    public function testGetByUserIdAndTargetTypeAndTargetId()
    {
        $favorite = $this->createFavorite();

        $result = $this->getDao()->getByUserIdAndTargetTypeAndTargetId($favorite['userId'], $favorite['targetType'], $favorite['targetId']);

        $this->assertEquals($favorite, $result);
    }

    protected function createFavorite($favorite = [])
    {
        $favorite = array_merge([
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => 'test type',
            'targetId' => 1,
        ], $favorite);

        return $this->getDao()->create($favorite);
    }

    /**
     * @return FavoriteDao
     */
    protected function getDao()
    {
        return $this->createDao('Favorite:FavoriteDao');
    }
}
