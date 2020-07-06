<?php

namespace Tests\Unit\Favorite\Service;

use Biz\BaseTestCase;
use Biz\Common\CommonException;
use Biz\Favorite\Dao\FavoriteDao;
use Biz\Favorite\FavoriteException;
use Biz\Favorite\Service\FavoriteService;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Biz\User\UserException;
use Topxia\Service\Common\ServiceKernel;

class FavoriteServiceTest extends BaseTestCase
{
    public function testCreateFavorite_whenTargetTypeMissing_thenThrowException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');

        $this->getFavoriteService()->createFavorite(['targetType' => null, 'targetId' => '1']);
    }

    public function testCreateFavorite_whenTargetIdMissing_thenThrowException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');

        $this->getFavoriteService()->createFavorite(['targetType' => 'test type', 'targetId' => null]);
    }

    public function testCreateFavorite_whenUserNotLogin_thenThrowException()
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('exception.user.unlogin');

        $this->mockCurrentUser();
        $this->getFavoriteService()->createFavorite(['targetType' => 'testType', 'targetId' => 1]);
    }

    public function testCreateFavorite_ifFavoriteExisted()
    {
        $existed = $this->createFavorite();
        $result = $this->getFavoriteService()->createFavorite($existed);
        $this->assertEquals($existed, $result);
    }

    public function testCreateFavorite()
    {
        $favoriteWithCurrentUser = $this->getFavoriteService()->createFavorite(['targetType' => 'testType', 'targetId' => 1]);
        $this->assertEquals('testType', $favoriteWithCurrentUser['targetType']);
        $this->assertEquals('1', $favoriteWithCurrentUser['targetId']);
        $this->assertEquals($this->getCurrentUser()->getId(), $favoriteWithCurrentUser['userId']);
    }

    public function testDeleteUserFavorite_whenForbidden_thenThrowException()
    {
        $favorite = $this->createFavorite();

        $this->expectException(FavoriteException::class);
        $this->expectExceptionMessage('exception.favorite.forbidden_operate_favorite');

        $this->mockCurrentUser();
        $this->getFavoriteService()->deleteUserFavorite($favorite['id'], $favorite['targetType'], $favorite['targetId']);
    }

    public function testDeleteUserFavorite()
    {
        $resultWhenFavoriteNotExist = $this->getFavoriteService()->deleteUserFavorite(1, 'targetType', 1);
        $this->assertTrue($resultWhenFavoriteNotExist);

        $favorite = $this->createFavorite();
        $before = $this->getFavoriteDao()->get($favorite['id']);

        $result = $this->getFavoriteService()->deleteUserFavorite($favorite['userId'], $favorite['targetType'], $favorite['targetId']);

        $after = $this->getFavoriteDao()->get($favorite['id']);
        $this->assertEquals($before, $favorite);
        $this->assertTrue($result);
        $this->assertNull($after);
    }

    protected function mockCurrentUser($user = [])
    {
        $user = array_merge(['id' => 0,
            'nickname' => '游客',
            'currentIp' => '',
            'roles' => [],
        ], $user);

        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        ServiceKernel::instance()->setCurrentUser($currentUser);
    }

    protected function createFavorite($favorite = [])
    {
        $favorite = array_merge([
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => 'test type',
            'targetId' => '1',
        ], $favorite);

        return $this->getFavoriteDao()->create($favorite);
    }

    /**
     * @return FavoriteService
     */
    protected function getFavoriteService()
    {
        return $this->createService('Favorite:FavoriteService');
    }

    /**
     * @return FavoriteDao
     */
    protected function getFavoriteDao()
    {
        return $this->createDao('Favorite:FavoriteDao');
    }
}
