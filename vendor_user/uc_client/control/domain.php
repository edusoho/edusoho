<?php

/*
    [UCenter] (C)2001-2099 Comsenz Inc.
    This is NOT a freeware, use is subject to license terms

    $Id: domain.php 1059 2011-03-01 07:25:09Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class domaincontrol extends base
{
    public function __construct()
    {
        $this->domaincontrol();
    }

    public function domaincontrol()
    {
        parent::__construct();
        $this->init_input();
        $this->load('domain');
    }

    public function onls()
    {
        return $_ENV['domain']->get_list(1, 9999, 9999);
    }
}
