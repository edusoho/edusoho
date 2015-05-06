<?php
namespace Topxia\Common;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class BlockToolkit
{

    public static function init($code, $jsonFile, $appType, $container = null)
    {

        if (file_exists($jsonFile)) {
            $blockMeta = json_decode(file_get_contents($jsonFile), true);
            if (empty($blockMeta)) {
                throw new \RuntimeException("插件元信息文件{$blockMeta}格式不符合JSON规范，解析失败，请检查元信息文件格式");
            }

            $blockService = ServiceKernel::instance()->createService('Content.BlockService');
            $block = array();
            foreach ($blockMeta as $key => $meta) {
                $block = $blockService->getBlockByCode($key);
                $default = array();
                foreach ($meta['items'] as $i => $item) {
                    $default[$i] = $item['default'];
                }
                if (empty($block)) {
                    $block = array(
                        'code' => $key,
                        'category' => $code,
                        'meta' => $meta,
                        'data' => $default,
                        'templateName' => $meta['templateName'],
                        'title' => $meta['title'],
                    );
                    $block = $blockService->createBlock($block);
                } else {
                    $block = $blockService->updateBlock($block['id'], array(
                        'category' => $code,
                        'meta' => $meta,
                        'data' => $default,
                        'templateName' => $meta['templateName'],
                        'title' => $meta['title'],
                    ));
                }

                if (empty($block['content'])) {
                    $container->enterScope('request');
                    $container->set('request', new Request(), 'request');

                    if (!in_array($appType, array('theme', 'plugin'))) {
                        throw new \RuntimeException("参数不正确!必须是主题和插件!");
                    }

                    if ($appType == 'theme') {
                        $content =  $container->get('templating')->render("@{$appType}s/{$code}/TopxiaWebBundle/views/Block/{$block['templateName']}", array('block' => $block));
                    }

                    if ($appType == 'plugin') {
                        $content =  $container->get('templating')->render("@{$appType}s/{$code}/{$code}Bundle/Resources/views/Block/{$block['templateName']}", array('block' => $block));
                    }
                    
                    $blockService->updateContent($block['id'], $content);
                }
            }

        }

        
    }

    public static function render($block)
    {
        
    }

    public static function updateCarousel($code)
    {
        $blockService = ServiceKernel::instance()->createService('Content.BlockService');
        $block = $blockService->getBlockByCode($code);
        $data = $block['data'];
        $content = $block['content'];
        preg_match_all('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $imgMatchs);
        preg_match_all('/< *img[^>]*alt *= *["\']?([^"\']*)/i', $content, $altMatchs);
        preg_match_all('/< *a[^>]*href *= *["\']?([^"\']*)/i', $content, $linkMatchs);
        preg_match_all('/< *a[^>]*target *= *["\']?([^"\']*)/i', $content, $targetMatchs);
        foreach ($data['carousel'] as $key => &$imglink) {
            if (!empty($imgMatchs[1][$key])) {
                $imglink['src'] = $imgMatchs[1][$key];
            }

            if (!empty($altMatchs[1][$key])) {
                $imglink['alt'] = $altMatchs[1][$key];
            }

            if (!empty($linkMatchs[1][$key])) {
                $imglink['href'] = $linkMatchs[1][$key];
            }

            if (!empty($targetMatchs[1][$key])) {
                $imglink['target'] = $targetMatchs[1][$key];
            }

        }

        $blockService->updateBlock($block['id'], array(
            'data' => $data,
        ));
    }

    public static function updateLinks($code)
    {
        $blockService = ServiceKernel::instance()->createService('Content.BlockService');
        $block = $blockService->getBlockByCode($code);
        $data = $block['data'];
        $content = $block['content'];
        preg_match_all('/<dt>(.*?)<\/dt>/i', $content, $textMatchs);
        preg_match_all('/<dl>.*?<\/dl>/i', $content, $dlMatchs);
        $index = 0;
        $index2 = 0;
        foreach ($data as $key => &$object) {
            if ($object['type'] == 'text') {
                $object['items'][0]['value'] = $textMatchs[1][$index++];
            }

            if ($object['type'] == 'link' && !empty($dl = $dlMatchs[0][$index2++])) {
                preg_match_all('/< *a[^>]*href *= *["\']?([^"\']*)/i', $dl, $hrefMatchs);
                preg_match_all('/< *a[^>]*target *= *["\']?([^"\']*)/i', $dl, $targetMatchs);
                preg_match_all('/< *a.*?>(.*?)<\/a>/i', $dl, $valuetMatchs);
                foreach ($object['items'] as $i => &$item) {
                    if (!empty($hrefMatchs[1][$i])) {
                        $item['href'] = $hrefMatchs[1][$i];
                    }

                    if (!empty($targetMatchs[1][$i])) {
                        $item['target'] = $targetMatchs[1][$i];
                    }

                    if (!empty($valuetMatchs[1][$i])) {
                        $item['value'] = $valuetMatchs[1][$i];
                    }
                }
            }
        }

        $blockService->updateBlock($block['id'], array(
            'data' => $data,
        ));
    }
}
