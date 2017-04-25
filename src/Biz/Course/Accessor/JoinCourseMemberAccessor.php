<?php

namespace Biz\Course\Accessor;

use Biz\Accessor\AccessorAdapter;

class JoinCourseMemberAccessor extends AccessorAdapter
{
    public function access($course)
    {
        $user = $this->getCurrentUser();
        if (empty($user) || !$user->isLogin()) {
            return $this->buildResult('user.not_login');
        }

        if ($user['locked']) {
            return $this->buildResult('user.locked');
        }

        return null;
    }
}
