<?php

namespace Biz\Favorite\Service;

interface FavoriteService
{
    public function getUserFavorite($userId, $targetType, $targetId);

    public function createFavorite(array $favorite);

    public function deleteUserFavorite($userId, $targetType, $targetId);

    public function countFavorites($conditions);

    public function searchFavorites($conditions, $orderBys, $start, $limit, $columns = []);
}
