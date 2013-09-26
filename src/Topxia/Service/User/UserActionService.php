<?php
namespace Topxia\Service\User;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UserActionService
{
    
	public function getUserAction($id);

	public function findUserActionsByIds(array $ids);

    public function searchUserActions(array $conditions, array $orderBy, $start, $limit);

    public function searchUserActionCount(array $conditions);

}