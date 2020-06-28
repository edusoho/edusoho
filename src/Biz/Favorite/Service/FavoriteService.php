<?php

namespace Biz\Favorite\Service;

interface FavoriteService
{
    public function getUserFavorite($userId, $targetType, $targetId);

    public function createFavorite($favorite);

    public function deleteUserFavorite($userId, $targetType, $targetId);

    public function isUserFavorite($userId, $targetType, $targetId);

    public function countFavorites($conditions);

    public function searchFavorites($conditions, $orderBys, $start, $limit, $columns = []);
}
