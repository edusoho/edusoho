<?php

namespace Codeages\Biz\Framework\Context;

interface CurrentUserInterface
{
    public function getUsername();

    public function getRoles();

    public function getPassword();

    public function getSalt();
}
