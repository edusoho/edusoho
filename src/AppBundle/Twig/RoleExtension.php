<?php

namespace AppBundle\Twig;

class RoleExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('not_update_roles', [$this, 'getNotUpdateRoles']),
        ];
    }

    public function getNotUpdateRoles()
    {
        return ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_TEACHER', 'ROLE_USER', 'ROLE_TEACHER_ASSISTANT', 'ROLE_EDUCATIONAL_ADMIN', 'ROLE_MARKETING_MALL_ADMIN'];
    }
}
