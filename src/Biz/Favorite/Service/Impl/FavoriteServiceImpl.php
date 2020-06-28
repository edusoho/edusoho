<?php

namespace Biz\Favorite\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Favorite\Dao\FavoriteDao;
use Biz\Favorite\Service\FavoriteService;
use Biz\User\UserException;

class FavoriteServiceImpl extends BaseService implements FavoriteService
{
    public function getUserFavorite($userId, $targetType, $targetId)
    {
        return $this->getFavoriteDao()->getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);
    }

    public function createFavorite($favorite)
    {
        if (!ArrayToolkit::requireds($favorite, ['targetType', 'targetId'], true)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (empty($favorite['userId']) && !$this->getCurrentUser()->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $favorite['userId'] = empty($favorite['userId']) ? $this->getCurrentUser()->getId() : $favorite['userId'];

        if ($this->isUserFavorite($favorite['userId'], $favorite['targetType'], $favorite['targetId'])) {
            return $this->getUserFavorite($favorite['userId'], $favorite['targetType'], $favorite['targetId']);
        }

        $favorite = ArrayToolkit::parts($favorite, ['userId', 'targetType', 'targetId']);

        return $this->getFavoriteDao()->create($favorite);
    }

    public function deleteUserFavorite($userId, $targetType, $targetId)
    {
        $existed = $this->getFavoriteDao()->getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);

        if (empty($existed)) {
            return true;
        }

        $this->getFavoriteDao()->delete($existed['id']);

        return true;
    }

    public function isUserFavorite($userId, $targetType, $targetId)
    {
        $favorite = $this->getUserFavorite($userId, $targetType, $targetId);

        return !empty($favorite);
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
