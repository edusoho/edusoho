<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\BlockToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme();

            $this->initBlock();

            $this->removeClassroomPlugin();
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
    }

    private function initBlock()
    {
        global $kernel;
        BlockToolkit::init(realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/themes/jianmo/block.json"), $kernel->getContainer());
    }

    private function removeClassroomPlugin()
    {
        ///删除班级插件
        $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../plugins/Classroom");
        $filesystem = new Filesystem();

        if (!empty($dir)) {
            $filesystem->remove($dir);
        }
    }

    private function updateScheme()
    {
        $connection = $this->getConnection();

        ///各个默认主题的编辑区
        $connection->exec("UPDATE `block` SET `code` = 'default:home_top_banner' WHERE `code` = 'home_top_banner';");
        if(!$this->isBlockExist("default-b:home_top_banner")){
            $connection->exec("INSERT INTO `block`( `userId`, `title`, `mode`, `template`, `templateName`, `templateData`, `content`, `code`, `meta`, `data`, `tips`, `createdTime`, `updateTime`, `category`) select `userId`, `title`, `mode`, `template`, `templateName`, `templateData`, `content`, 'default-b:home_top_banner', `meta`, `data`, `tips`, `createdTime`, `updateTime`, `category` from `block` where `code`='default:home_top_banner';");
        }
        $connection->exec("UPDATE `block` SET templateName='@theme/default/block/home_top_banner.template.html.twig' where code='default:home_top_banner';");
        $connection->exec("UPDATE `block` SET templateName='@theme/default-b/block/home_top_banner.template.html.twig' where code='default-b:home_top_banner';");

        ///TODO：老课程复制的内容


        ///数据升级
        //$connection->exec("UPDATE status set courseId=objectId where objectType='course';");
        //$connection->exec("UPDATE status s set s.courseId=(select courseId from course_lesson where id=s.objectId) where s.objectType='lesson';");

        //$connection->exec("UPDATE status s set s.classroomId=(select classroomId from classroom_courses where courseId = s.courseId limit 0,1) where s.courseId in (select courseId from classroom_courses);");
        //$connection->exec("UPDATE status s set s.classroomId=s.objectId where s.objectType='classroom';");

        if($this->isTableExist("homework")){
            $connection->exec("UPDATE course_lesson cl set homeworkId=(select id from homework where lessonId=cl.id limit 0,1) where cl.id in (select lessonId from homework);");
        }

        if($this->isTableExist("exercise")){
            $connection->exec("UPDATE course_lesson cl set exerciseId=(select id from exercise where lessonId=cl.id limit 0,1) where cl.id in (select lessonId from exercise);");
        }
        
        $connection->exec("UPDATE course c set c.noteNum=(select count(*) from course_note where courseId = c.id);");

        $connection->exec("UPDATE classroom c set c.noteNum=(
            select sum(noteNum) from course where id in (
                select courseId from classroom_courses where classroomId = c.id
            )
        ) where c.id in (select a.classroomId from (select sum(noteNum) as notNum,classroomId from classroom_courses left join course cc on courseId=cc.id) a where a.notNum>0 and a.notNum is not null)");

        $connection->exec("UPDATE classroom_courses cc set parentCourseId=(select parentId from course where id=cc.courseId) where courseId in (select id from course where parentId is not null);");

        $connection->exec("update thread set solved=1 where id in (
          select distinct(threadpost.threadId) from (
            select tp.id, tp.userId, t.targetId as classroomId,t.id as threadId from thread_post tp left join thread t on tp.threadId=t.id
          ) threadpost, 
          (
            select classroomId, userId FROM `classroom_member` WHERE role in ('teacher', 'headTeacher', 'assistant', 'studentAssistant')
          ) classroomMember 
          where threadpost.userId=classroomMember.userId and threadpost.classroomId=classroomMember.classroomId
        );");

        $connection->exec("update thread_post set adopted=1 where id in (
          select distinct(threadpost.id) from (
            select tp.id, tp.userId, t.targetId as classroomId,t.id as threadId from thread_post tp left join thread t on tp.threadId=t.id
          ) threadpost, 
          (
            select classroomId, userId FROM `classroom_member` WHERE role in ('teacher', 'headTeacher', 'assistant', 'studentAssistant')
          ) classroomMember 
          where threadpost.userId=classroomMember.userId and threadpost.classroomId=classroomMember.classroomId
        ) and parentId=0;");

    }
    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isThemeBlockInit($code)
    {
        $sql = "select * from block where code='{$code}:home_top_banner';";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isFileGroupExist($code)
    {
        $sql = "select * from file_group where code='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isBlockExist($code)
    {
        $sql = "select * from block where code='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isCrontabJobExist($code)
    {
        $sql = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    private function updateArticle($connection)
    {
        $sth = $connection->prepare("SELECT * FROM article");
        $sth->execute();
        while ($article = $sth->fetch(PDO::FETCH_ASSOC)) {
            $tagIds = json_decode($article['tagIds'], true);
            $tagIds = '|' . implode('|', $tagIds) . '|';
            $connection->exec("UPDATE article SET tagIds = \"{$tagIds}\" WHERE id = " . $article['id']);
        }
    }
}

abstract class AbstractUpdater
{
    protected $kernel;
    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    abstract public function update();
}
