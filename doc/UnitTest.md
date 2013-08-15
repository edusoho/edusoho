# 单元测试使用说明

测试环境下，读取的配置文件是app/config_test.yml

本项目跑单元测试的数据库名为：edusoho_test, 该数据库名请自行创建： CREATE DATABASE `edusoho_test`

* 运行所有测试
phpunit -c app/  --coverage-html
  -c 指定phpunit读取配置文件的目录，默认读取的是phpunit.xml.dist

* 运行某个目录下的所有测试，例如
  phpunit -c app/ src/Topxia/Service/User/Tests/

* 运行某个测试，例如
phpunit -c app/ src/Topxia/Service/Content/Tests/FileServiceTest.php
phpunit -c app/ src/Topxia/Service/Content/Tests/CommentServiceTest.php
phpunit -c app/ src/Topxia/Service/Content/Tests/BlockServiceTest.php

phpunit -c app/ src/Topxia/Service/Course/Tests/CourseServiceTest.php
phpunit -c app/ src/Topxia/Service/Course/Tests/QuizServiceTest.php
phpunit -c app/ src/Topxia/Service/Course/Tests/ReviewServiceTest.php
phpunit -c app/ src/Topxia/Service/Course/Tests/MaterialServiceTest.php
phpunit -c app/ src/Topxia/Service/Course/Tests/ThreadServiceTest.php
phpunit -c app/ src/Topxia/Service/Course/Tests/NoteServiceTest.php
phpunit -c app/ src/Topxia/Service/Course/Tests/OrderServiceTest.php
phpunit -c app/ src/Topxia/Service/Course/Tests/AnnouncementServiceTest.php

phpunit -c app/ src/Topxia/Service/System/Tests/CacheServiceTest.php
phpunit -c app/ src/Topxia/Service/System/Tests/SettingServiceTest.php

phpunit -c app/ src/Topxia/Service/Taxonomy/Tests/CategoryServiceTest.php
phpunit -c app/ src/Topxia/Service/Taxonomy/Tests/TagServiceTest.php
phpunit -c app/ src/Topxia/Service/Taxonomy/Tests/LocationServiceTest.php

phpunit -c app/ src/Topxia/Service/User/Tests/MessageServiceTest.php
phpunit -c app/ src/Topxia/Service/User/Tests/NotificationServiceTest.php
phpunit -c app/ src/Topxia/Service/User/Tests/TrackServiceTest.php
phpunit -c app/ src/Topxia/Service/User/Tests/UserServiceTest.php

phpunit -c app/ src/Topxia/Service/Util/Tests/MediaParseServiceTest.php

