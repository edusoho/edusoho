<?php

namespace AppBundle\Common;

class ServiceToolkit
{
    private static $services = array(
        'homeworkReview' => array(
            'code' => 'homeworkReview',
            'shortName' => '练',
            'fullName' => '24小时作业批阅',
            'summary' => '24小时内完成作业批阅，即时反馈并巩固您的学习效果',
            'active' => 0,
        ),
        'testpaperReview' => array(
            'code' => 'testpaperReview',
            'shortName' => '试',
            'fullName' => '24小时阅卷点评',
            'summary' => '24小时内批阅您提交的试卷，给予有针对性的点评',
            'active' => 0,
        ),
        'teacherAnswer' => array(
            'code' => 'teacherAnswer',
            'shortName' => '问',
            'fullName' => '提问必答',
            'summary' => '对于提问做到有问必答，帮您扫清学习过程中的种种障碍',
            'active' => 0,
        ),
        'liveAnswer' => array(
            'code' => 'liveAnswer',
            'shortName' => '疑',
            'fullName' => '一对一在线答疑',
            'summary' => '提供专属的一对一在线答疑，快速答疑解惑。',
            'active' => 0,
        ),
        'event' => array(
            'code' => 'event',
            'shortName' => '动',
            'fullName' => '班级活动',
            'summary' => '不定期组织各种线上或线下的班级活动，让学习更加生动有趣，同学关系更为紧密',
            'active' => 0,
        ),
        'workAdvise' => array(
            'code' => 'workAdvise',
            'shortName' => '业',
            'fullName' => '就业指导',
            'summary' => '完成全部学习后，老师对您的学习成果和能力水平给出评估，并提供专业化的就业指导',
            'active' => 0,
        ),
    );

    public static function getServicesByCodes($codes)
    {
        return array_values(ArrayToolkit::parts(static::$services, $codes));
    }
}
