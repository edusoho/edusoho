<?php

define("FOO",     "something");


class User
{
    const NAME = 'zhangsan';
    const AGE = '18';


    public function do()
    {
        self::NAME;
        User::NAME;
    }
}


User::NAME;
