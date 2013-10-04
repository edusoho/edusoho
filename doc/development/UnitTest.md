# 单元测试使用说明

测试环境下，读取的配置文件是app/config_test.yml

本项目跑单元测试的数据库名为：edusoho_test, 该数据库名请自行创建： CREATE DATABASE `edusoho_test`

* 运行所有测试
phpunit -c app/  --coverage-html
  -c 指定phpunit读取配置文件的目录，默认读取的是phpunit.xml.dist

* 运行某个目录下的所有测试，例如
  phpunit -c app/ src/Topxia/Service/User/Tests/

* 运行某个测试，例如
phpunit -c app/ src/Topxia/Service/Course/Tests/CourseServiceTest.php
phpunit -c app/ src/Topxia/Service/User/Tests/UserServiceTest.php
phpunit -c app/ src/Topxia/Service/Content/Tests/CommentServiceTest.php
phpunit -c app/ src/Topxia/Service/Content/Tests/ContentServiceTest.php
phpunit -c app/ src/Topxia/Service/Content/Tests/BlockServiceTest.php
phpunit -c app/ src/Topxia/Service/Upgrade/Tests/UpgradeServiceTest.php
