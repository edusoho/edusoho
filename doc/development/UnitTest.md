# 单元测试使用说明

测试环境下，读取的配置文件是app/config_test.yml

本项目跑单元测试的数据库名为：edusoho_test, 该数据库名请自行创建： CREATE DATABASE `edusoho_test`


* 默认当前用户信息：

    'nickname' => 'admin',
    'email' => 'admin@admin.com',
    'password'=>'admin',
    'loginIp' => '127.0.0.1',
    'roles' => array('ROLE_USER','ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER')

* 运行所有测试

phpunit -c app/  

遇到错误或者遇到失败就停止
phpunit --stop-on-error   --stop-on-fail -c app/

-c 指定phpunit读取配置文件的目录，默认读取的是phpunit.xml.dist

* 运行某个目录下的所有测试，例如
  phpunit -c app/ src/Topxia/Service/User/Tests/

* 运行某个测试，例如
phpunit -c app/ src/Topxia/Service/MoneyCard/Tests/MoneyCardServiceTest.php


phpunit -c app/ src/Topxia/Service/Course/Tests/CourseServiceTest.php

phpunit --stop-on-error --stop-on-fail -c app/ src/Topxia/Service/Quiz/Tests/QuestionServiceTest.php

phpunit -c app/ src/Topxia/Service/User/Tests/LoginRecordServiceTest.php

phpunit -c app/ src/Topxia/Service/Course/Tests/AnnouncementServiceTest.php
phpunit -c app/ src/Topxia/Service/User/Tests/UserServiceTest.php
phpunit -c app/ src/Topxia/Service/Content/Tests/CommentServiceTest.php
phpunit -c app/ src/Topxia/Service/Content/Tests/ContentServiceTest.php
phpunit -c app/ src/Topxia/Service/Content/Tests/BlockServiceTest.php
phpunit -c app/ src/Topxia/Service/Upgrade/Tests/UpgradeServiceTest.php
