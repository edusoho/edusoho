<?php

/*
    [UCenter] (C)2001-2099 Comsenz Inc.
    This is NOT a freeware, use is subject to license terms

    $Id: app.php 1059 2011-03-01 07:25:09Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class appcontrol extends base
{
    public function __construct()
    {
        $this->appcontrol();
    }

    public function appcontrol()
    {
        parent::__construct();
        $this->load('app');
    }

    public function onls()
    {
        $this->init_input();
        $applist = $_ENV['app']->get_apps('appid, type, name, url, tagtemplates, viewprourl, synlogin');
        $applist2 = array();
        foreach ($applist as $key => $app) {
            $app['tagtemplates'] = $this->unserialize($app['tagtemplates']);
            $applist2[$app['appid']] = $app;
        }

        return $applist2;
    }

    public function onadd()
    {
    }

    public function onucinfo()
    {
    }

    public function _random($length, $numeric = 0)
    {
    }

    public function _generate_key()
    {
    }

    public function _format_notedata($notedata)
    {
    }
}
