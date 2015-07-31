<?php

namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Common\BlockToolkit;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;

class TestBlockCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('topxia:testblock')
            ->setDescription('测试老数据转换');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始初始化系统</info>');
        $this->initServiceKernel();
        $block = $this->getOldBlockData($output);

        $this->replaceData($block, $output);

        $this->replaceMetaItems($block, $output);

        $this->replaceContent($block, $output);

        $block = $this->getBlockService()->updateBlock($block['id'],$block);
        $output->writeln('<info>简墨编辑区替换完成</info>');
    }


    protected function replaceData(&$block,$output)
    {
        $output->writeln('<info>替换DATA数据</info>');
        $oldData = $block['data'];
        $newHtml = $this->oldDataConvertHtml($oldData);
        $newData = array(
            'posters' => array(
                array(
                    'src' => $oldData['carousel1ground'][0]['src'],
                    'alt' => "海报1",
                    'layout' => 'limitWide',
                    'background' => $oldData['carousel1background'][0]['value'],
                    'href' => $oldData['carousel1ground'][0]['href'],
                    'html' => $newHtml[0],
                    'status' => 1,
                    'mode' => 'html'
                ), array(
                    'src' => $oldData['carousel2ground'][0]['src'],
                    'alt' => "海报2",
                    'layout' => 'limitWide',
                    'background' => $oldData['carousel2background'][0]['value'],
                    'href' => $oldData['carousel2ground'][0]['href'],
                    'html' => $newHtml[1],
                    'status' => 1,
                    'mode' => 'html'
                ), array(
                    'src' => $oldData['carousel3ground'][0]['src'],
                    'alt' => "海报3",
                    'layout' => 'limitWide',
                    'background' => $oldData['carousel3background'][0]['value'],
                    'href' => $oldData['carousel3ground'][0]['href'],
                    'html' => $newHtml[2],
                    'status' => 1,
                    'mode' => 'html'
                ), array(
                    'src' => '/assets/v2/img/poster_bg.jpg',
                    'alt' => "海报4",
                    'layout' => 'limitWide',
                    'background' => "#3F3F3F",
                    'href' => "",
                    'html' => "",
                    'status' => 0,
                    'mode' => 'img'
                ), array(
                    'src' => '/assets/v2/img/poster_bg.jpg',
                    'alt' => "海报5",
                    'layout' => 'limitWide',
                    'background' => "#3F3F3F",
                    'href' => "",
                    'html' => "",
                    'status' => 0,
                    'mode' => 'img'
                ), array(
                    'src' => '/assets/v2/img/poster_bg.jpg',
                    'alt' => "海报6",
                    'layout' => 'limitWide',
                    'background' => "#3F3F3F",
                    'href' => "",
                    'html' => "",
                    'status' => 0,
                    'mode' => 'img'
                ), array(
                    'src' => '/assets/v2/img/poster_bg.jpg',
                    'alt' => "海报7",
                    'layout' => 'limitWide',
                    'background' => "#3F3F3F",
                    'href' => "",
                    'html' => "",
                    'status' => 0,
                    'mode' => 'img'
                ), array(
                    'src' => '/assets/v2/img/poster_bg.jpg',
                    'alt' => "海报8",
                    'layout' => 'limitWide',
                    'background' => "#3F3F3F",
                    'href' => "",
                    'html' => "",
                    'status' => 0,
                    'mode' => 'img'
                ),
            )
        );

        $block['data'] = $newData;
    }

    protected function replaceMetaItems(&$block, $output)
    {
        $output->writeln('<info>替换META数据</info>');
        $default = array();
        foreach ( $block['data']['posters'] as $poster){
            $default[] = $poster;
        }
        $newItems = array(
            "title" => "海报",
            "desc" => "首页海报",
            "count" => 1,
            "type" => "poster",
            "default" => $default
        );

        $block['meta']['items'] = array(
            "posters" => $newItems
        );
    }

    protected function replaceContent(&$block, $output)
    {
        $output->writeln('<info>替换CONTENT数据</info>');

        $newContent = BlockToolkit::render($block, $this->getContainer());
        $block['content'] = $newContent;
    }

    protected function oldDataConvertHtml($oldData)
    {
        $html1 = !empty($oldData['carousel1banner']) ?
            "<div class=\"swiper-slide\" style=\"background: {$oldData['carousel1background'][0]['value']};\">
                <div class=\"container\">
                    <a href=\"{$oldData['carousel1ground'][0]['href']}\" target=\"_blank\" >
                        <img class=\"img-responsive\" src=\"{$oldData['carousel1ground'][0]['src']}\">
                        <div class=\"mask\">
                            <div class=\"title\">
                                <span>{$oldData['carousel1title1'][0]['value']}</span><span>{$oldData['carousel1title2'][0]['value']}</span>
                            </div>
                            <div class=\"subtitle\">
                                <span>{$oldData['carousel1subtitle1'][0]['value']}</span><span>{$oldData['carousel1subtitle2'][0]['value']}</span>
                            </div>
                            <div class=\"item-mac\">
                                <img class=\"img-responsive\" src=\"{$oldData['carousel1banner'][0]['src']}\">
                            </div>
                        </div>
                    </a>
                </div>
            </div>"
            :
            "<div class=\"swiper-slide\" style=\"background: {$oldData['carousel1background'][0]['value']};\">
                <div class=\"container\">
                    <a href=\"{$oldData['carousel1ground'][0]['href']}\" target=\"_blank\" >
                        <img class=\"img-responsive\" src=\"{$oldData['carousel1ground'][0]['src']}\">
                        <div class=\"mask\">
                            <div class=\"title\">
                                <span>{$oldData['carousel1title1'][0]['value']}</span><span>{$oldData['carousel1title2'][0]['value']}</span>
                            </div>
                            <div class=\"subtitle\">
                                <span>{$oldData['carousel1subtitle1'][0]['value']}</span><span>{$oldData['carousel1subtitle2'][0]['value']}</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>";

        $html2 = !empty($oldData['carousel2banner']) ?
            "<div class=\"swiper-slide\" style=\"background: {$oldData['carousel2background'][0]['value']};\">
                <div class=\"container\">
                    <a href=\"{$oldData['carousel2ground'][0]['href']}\" target=\"_blank\" >
                        <img class=\"img-responsive\" src=\"{$oldData['carousel2ground'][0]['src']}\">
                        <div class=\"mask\">
                            <div class=\"title\">
                                <span>{$oldData['carousel2title1'][0]['value']}</span><span>{$oldData['carousel2title2'][0]['value']}</span>
                            </div>
                            <div class=\"subtitle\">
                                <span>{$oldData['carousel2subtitle1'][0]['value']}</span><span>{$oldData['carousel2subtitle2'][0]['value']}</span>
                            </div>
                            <div class=\"item-mac\">
                                <img class=\"img-responsive\" src=\"{$oldData['carousel2banner'][0]['src']}\">
                            </div>
                        </div>
                    </a>
                </div>
            </div>"
            :
            "<div class=\"swiper-slide\" style=\"background: {$oldData['carousel2background'][0]['value']};\">
                <div class=\"container\">
                    <a href=\"{$oldData['carousel2ground'][0]['href']}\" target=\"_blank\" >
                        <img class=\"img-responsive\" src=\"{$oldData['carousel2ground'][0]['src']}\">
                        <div class=\"mask\">
                            <div class=\"title\">
                                <span>{$oldData['carousel2title1'][0]['value']}</span><span>{$oldData['carousel2title2'][0]['value']}</span>
                            </div>
                            <div class=\"subtitle\">
                                <span>{$oldData['carousel2subtitle1'][0]['value']}</span><span>{$oldData['carousel2subtitle2'][0]['value']}</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>";

        $html3 = !empty($oldData['carousel3banner']) ?
            "<div class=\"swiper-slide\" style=\"background: {$oldData['carousel3background'][0]['value']};\">
                <div class=\"container\">
                    <a href=\"{$oldData['carousel3ground'][0]['href']}\" target=\"_blank\" >
                        <img class=\"img-responsive\" src=\"{$oldData['carousel3ground'][0]['src']}\">
                        <div class=\"mask\">
                            <div class=\"title\">
                                <span>{$oldData['carousel3title1'][0]['value']}</span><span>{$oldData['carousel3title2'][0]['value']}</span>
                            </div>
                            <div class=\"subtitle\">
                                <span>{$oldData['carousel3subtitle1'][0]['value']}</span><span>{$oldData['carousel3subtitle2'][0]['value']}</span>
                            </div>
                            <div class=\"item-mac\">
                                <img class=\"img-responsive\" src=\"{$oldData['carousel3banner'][0]['src']}\">
                            </div>
                        </div>
                    </a>
                </div>
            </div>"
            :
            "<div class=\"swiper-slide\" style=\"background: {$oldData['carousel3background'][0]['value']};\">
                <div class=\"container\">
                    <a href=\"{$oldData['carousel3ground'][0]['href']}\" target=\"_blank\" >
                        <img class=\"img-responsive\" src=\"{$oldData['carousel3ground'][0]['src']}\">
                        <div class=\"mask\">
                            <div class=\"title\">
                                <span>{$oldData['carousel3title1'][0]['value']}</span><span>{$oldData['carousel3title2'][0]['value']}</span>
                            </div>
                            <div class=\"subtitle\">
                                <span>{$oldData['carousel3subtitle1'][0]['value']}</span><span>{$oldData['carousel3subtitle2'][0]['value']}</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>";


        return array($html1, $html2, $html3);
    }

    protected function getOldBlockData($output)
    {
        $output->writeln('<info>获取编辑区老数据</info>');
        $block = $this->getBlockService()->getBlockByCode('jianmo:home_top_banner');
        return $block;
    }

    protected function getBlockService()
    {
        return $this->getServiceKernel()->createService('Content.BlockService');
    }

    private function initServiceKernel()
    {
        $serviceKernel = ServiceKernel::create('dev', false);
        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());

        $serviceKernel->setConnection($this->getContainer()->get('database_connection'));
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array(),
        ));
        $serviceKernel->setCurrentUser($currentUser);
    }
}
