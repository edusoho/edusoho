<?php
namespace SensitiveWord\Service\UserLevel\Impl;

use Topxia\Service\Common\BaseService;
use SensitiveWord\Service\UserLevel\UserLevelService;

class UserLevelServiceImpl extends BaseService implements UserLevelService
{
    public function checkPostStatusByLevel()
    {
        $user = $this->getCurrentUser();

        if (in_array('ROLE_SUPER_ADMIN', $user['roles']) || in_array('ROLE_TEACHER', $user['roles'])) {
            return false;
        }

        $postThreadForbid = false;

        $counditions = array(
            'createdTime' => strtotime(date("Y-m-d ", time())),
            'userId'      => $user['id']
        );
        $todayThreadCount = $this->getGroupThreadService()->searchThreadsCount($counditions);
        $todyPostCount    = $this->getGroupThreadService()->searchPostsCount($counditions);

        if ($todayThreadCount > 50 || $todayThreadCount > 200) {
            $postThreadForbid = true;
        } else
        if ($this->getLevel($user['point']) <= 1) {
            if ($todayThreadCount >= 3 || $todayThreadCount >= 20) {
                $postThreadForbid = true;
            }
        } else
        if ($this->getLevel($user['point']) > 1 && $this->getLevel($user['point']) <= 5) {
            if ($todayThreadCount >= 5 || $todayThreadCount >= 30) {
                $postThreadForbid = true;
            }
        } else
        if ($this->getLevel($user['point']) > 5) {
            if ($todayThreadCount >= 10 || $todayThreadCount >= 50) {
                $postThreadForbid = true;
            }
        }

        return $postThreadForbid;
    }

    public function checkUserStatusByType($type)
    {
        $user = $this->getCurrentUser();

        if (in_array('ROLE_SUPER_ADMIN', $user['roles']) || in_array('ROLE_TEACHER', $user['roles'])) {
            return false;
        }

        $arrayFilter = $this->getArrayFilter();
        $iskey       = array_key_exists($type, $arrayFilter);

        if ($iskey) {
            $user        = $this->getCurrentUser();
            $time        = strtotime(date('Y-m-d', time()));
            $counditions = array(
                'userId'    => $user['id'],
                'startTime' => $time
            );
            $falg = false;

            switch ($type) {
                case 'lesson-comment':
                    $counditions['targetType'] = 'lesson';
                    $count                     = $this->getThreadService()->searchPostsCount($counditions);
                    break;

                case 'leave-message':
                    $counditions['targetType'] = 'user';
                    $count                     = $this->getThreadService()->searchPostsCount($counditions);
                    break;

                case 'course-question':
                    $count = $this->getCourseThreadService()->searchThreadCount($counditions);
                    break;

                case 'lesson-note':
                    $count = $this->getCourseNoteService()->searchNoteCount($counditions);
                    break;

                case 'user-feedback':
                    $counditions['targetType'] = 'feedback';
                    $count                     = $this->getAdvisoryService()->searchAdvisoryCount($counditions);
                    break;

                case 'message':
                    $counditions['startDate'] = $time;
                    $counditions['fromId']    = $user['id'];
                    $count                    = $this->getMessageService()->searchMessagesCount($counditions);
                    break;
            }

            return $count > $arrayFilter[$type];
        } else {
            return true; //如果页面参数被修改了，则直接返回true, 抛出异常
        }
    }

    //课时评论：总100条  留言：总100条  问答：30条  笔记：30条

    public function getArrayFilter()
    {
        return array(

            //课程问答
            'course-question' => '30',

            //用户留言
            'leave-message'   => '100',

            //课时评论
            'lesson-comment'  => '100',

            //课时笔记
            'lesson-note'     => '30',

            //用户反馈
            'user-feedback'   => '20',

            //用户失信
            'message'         => '50'
        );
    }

    /**
     * 注册1天内，并且是凌晨，不能发帖
     */
    public function checkPostStatusByDate()
    {
        $user = $this->getCurrentUser();

        if (time() - $user['createdTime'] > 86400) {
            return false;
        }

        $hour = date('H', time());

        if ($hour >= 0 && $hour < 8) {
            return true;
        }

        return true;
    }

    protected function getLevel($point)
    {
        foreach ($this->levelPoint() as $level => $levelPoint) {
            if ($point < $levelPoint) {
                return $level - 1;
            }
        }

        return max(array_keys($this->levelPoint()));
    }

    protected function getMessageService()
    {
        return $this->createService('User.MessageService');
    }

    protected function getGroupThreadService()
    {
        return $this->createService('Group.ThreadService');
    }

    protected function getThreadService()
    {
        return $this->createService('Thread.ThreadService');
    }

    protected function getCourseThreadService()
    {
        return $this->createService('Course.ThreadService');
    }

    protected function getCourseNoteService()
    {
        return $this->createService('Course.NoteService');
    }

    protected function getAdvisoryService()
    {
        return $this->createService('Custom:Advisory.AdvisoryService');
    }

    private function levelPoint()
    {
        return array(
            1  => 0,
            2  => 30,
            3  => 80,
            4  => 120,
            5  => 150,
            6  => 200,
            7  => 250,
            8  => 350,
            9  => 450,
            10 => 500,
            11 => 800,
            12 => 1200,
            13 => 1600,
            14 => 2000,
            15 => 2500,
            16 => 3500,
            17 => 4500,
            18 => 6000,
            19 => 7500,
            20 => 9000,
            21 => 12000,
            22 => 15000,
            23 => 18000,
            24 => 21000,
            25 => 25000,
            26 => 29000,
            27 => 34000,
            28 => 39000,
            29 => 43000,
            30 => 47000,
            31 => 52000,
            32 => 57000,
            33 => 62000,
            34 => 67000,
            35 => 72000,
            36 => 78000,
            37 => 84000,
            38 => 90000,
            39 => 96000,
            40 => 102000,
            41 => 110000,
            42 => 118000,
            43 => 126000,
            44 => 134000,
            45 => 142000,
            46 => 150000,
            47 => 160000,
            48 => 170000,
            49 => 180000,
            50 => 200000
        );
    }
}
