<?php

namespace Biz\Favorite\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Favorite\Dao\FavoriteDao;
use Biz\Favorite\FavoriteException;
use Biz\Favorite\Service\FavoriteService;
use Biz\User\UserException;

class FavoriteServiceImpl extends BaseService implements FavoriteService
{
    public function getUserFavorite($userId, $targetType, $targetId)
    {
        return $this->getFavoriteDao()->getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);
    }

    public function createFavorite(array $favorite)
    {
        if (!ArrayToolkit::requireds($favorite, ['targetType', 'targetId'], true)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (!$this->getCurrentUser()->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $favorite['userId'] = $this->getCurrentUser()->getId();

        $existed = $this->getUserFavorite($favorite['userId'], $favorite['targetType'], $favorite['targetId']);

        if (!empty($existed)) {
            return $existed;
        }

        $favorite = $this->getFavoriteDao()->create(ArrayToolkit::parts($favorite, ['userId', 'targetType', 'targetId']));
        $this->dispatch('favorite.create', $favorite);

        return $favorite;
    }

    public function deleteUserFavorite($userId, $targetType, $targetId)
    {
        $existed = $this->getFavoriteDao()->getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);

        if (empty($existed)) {
            return true;
        }

        if ($existed['userId'] != $this->getCurrentUser()->getId()) {
            $this->createNewException(FavoriteException::FORBIDDEN_OPERATE_FAVORITE());
        }

        $this->getFavoriteDao()->delete($existed['id']);

        return true;
    }

    public function countFavorites($conditions)
    {
        return $this->getFavoriteDao()->count($conditions);
    }

    public function searchFavorites($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getFavoriteDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    /**
     * @return FavoriteDao
     */
    protected function getFavoriteDao()
    {
        return $this->createDao('Favorite:FavoriteDao');
    }
}
