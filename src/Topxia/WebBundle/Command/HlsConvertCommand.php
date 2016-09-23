<?php
namespace Topxia\WebBundle\Command;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HlsConvertCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('hls:convert')
            ->addArgument('siteUrl', InputArgument::REQUIRED, 'site url')
            ->addArgument('courseId', InputArgument::REQUIRED, 'course id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $output->writeln('<info>开始HLS转码</info>');

        $courseId = $input->getArgument('courseId');
        $siteUrl  = $input->getArgument('siteUrl');

        if (strtolower($courseId) == 'all') {
            $courses   = $this->getCourseService()->searchCourses(array(), 'latest', 0, 10000);
            $courseIds = ArrayToolkit::column($courses, 'id');
        } else {
            $courseIds = array($courseId);
        }

        foreach ($courseIds as $courseId) {
            $this->convertCourse($courseId, $siteUrl, $output);
        }

        $output->writeln('<info>转码结束</info>');
    }

    protected function convertCourse($courseId, $siteUrl, $output)
    {
        $output->writeln("* 正在转码课程 #{$courseId}");
        $lessons = $this->getCourseService()->getCourseLessons($courseId);

        foreach ($lessons as $lesson) {
            if ($lesson['type'] != 'video') {
                continue;
            }

            if ($lesson['mediaSource'] != 'self') {
                continue;
            }

            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
            if (empty($file)) {
                continue;
            }

            if ($file['storage'] != 'cloud') {
                continue;
            }

            if (!empty($file['metas2'])) {
                continue;
            }

            $factory = new CloudClientFactory();
            $client  = $factory->createClient();

            $commands   = array_keys($client->getVideoConvertCommands());
            $convertKey = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 12);

            $callbackUrl = $siteUrl.$this->getContainer()->get('router')->generate('uploadfile_cloud_convert_callback', array('key' => $convertKey));

            $result = $client->convertVideo($file['hashId'], implode(';', $commands), $callbackUrl);
            if (empty($result['persistentId'])) {
                $output->writeln("\t[ERROR] 转码请求失败。(课时：{$lesson['title']} #{$lesson['id']}) ".json_encode($result));
            } else {
                $convertHash = "{$result['persistentId']}:{$convertKey}";
                $this->getUploadFileService()->setFileConverting($file['id'], $convertHash);
                $output->writeln("\t[OK] 转码请求成功。(课时：{$lesson['title']} #{$lesson['id']}) ");
            }
        }
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }
}