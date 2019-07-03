<?php

namespace AppBundle\Common;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\Exception\InvalidArgumentException;

class BlockToolkit
{
    public static function init($jsonFile, $container = null, $blocksFolder = null)
    {
        if (file_exists($jsonFile)) {
            $blockMeta = json_decode(file_get_contents($jsonFile), true);
            if (empty($blockMeta)) {
                throw new InvalidArgumentException(ServiceKernel::instance()->trans('插件元信息文件%blockMeta%格式不符合JSON规范，解析失败，请检查元信息文件格式',
                    array('%blockMeta%' => $blockMeta)));
            }

            $blockService = ServiceKernel::instance()->getBiz()->service('Content:BlockService');

            foreach ($blockMeta as $key => $meta) {
                $blockTemplate = $blockService->getBlockTemplateByCode($key);
                $default = array();
                foreach ($meta['items'] as $i => $item) {
                    $default[$i] = $item['default'];
                }
                $blockTemplateFields = array(
                    'code' => $key,
                    'mode' => 'template',
                    'category' => empty($meta['category']) ? 'system' : $meta['category'],
                    'meta' => $meta,
                    'data' => $default,
                    'templateName' => $meta['templateName'],
                    'title' => $meta['title'],
                );
                if (empty($blockTemplate)) {
                    $blockTemplate = $blockService->createBlockTemplate($blockTemplateFields);
                } else {
                    //unset($blockTemplateFields['code']);
                    $blockTemplate = $blockService->updateBlockTemplate($blockTemplate['id'], $blockTemplateFields);
                }

                if (!empty($blocksFolder)) {
                    $filename = $blocksFolder.'block-'.md5($key).'.html';
                    if (file_exists($filename)) {
                        $content = file_get_contents($filename);
                    } else {
                        $content = '';
                    }

                    $blockTemplate = $blockService->updateTemplateContent($blockTemplate['id'], $content);
                }

                if (empty($blockTemplate['content']) && $container) {
                    $content = self::render($blockTemplate, $container);
                    $blockService->updateTemplateContent($blockTemplate['id'], $content);
                }
            }
        }
    }

    public static function render($block, $container)
    {
        if (!$container->isScopeActive('request')) {
            $container->enterScope('request');
            $container->set('request', new Request(), 'request');
        }

        if (empty($block['templateName']) || empty($block['data'])) {
            return '';
        }

        return $container->get('templating')->render($block['templateName'], $block['data']);
    }

    public static function generateBlcokContent($metaFilePath, $dist, $container)
    {
        $metas = file_get_contents($metaFilePath);
        $metas = json_decode($metas, true);
        if (empty($metas)) {
            throw new InvalidArgumentException(ServiceKernel::instance()->trans('插件元信息文件%metaFilePath%格式不符合JSON规范，解析失败，请检查元信息文件格式', array('%metaFilePath%' => $metaFilePath)));
        }

        foreach ($metas as $code => $meta) {
            $data = array();
            foreach ($meta['items'] as $key => $item) {
                $data[$key] = $item['default'];
            }
            $blockTemplate = array('templateName' => $meta['templateName'], 'data' => $data);
            $html = self::render($blockTemplate, $container);

            $filename = 'block-'.md5($code).'.html';
            if (!file_exists($dist)) {
                mkdir($dist);
            }

            $filename = "{$dist}/".$filename;

            file_put_contents($filename, $html);
        }
    }

    public static function updateCarousel($code)
    {
        $blockService = ServiceKernel::instance()->getBiz()->service('Content:BlockService');
        $blockTemplate = $blockService->getBlockTemplateByCode($code);
        $data = $blockTemplate['data'];
        $content = $blockTemplate['content'];

        preg_match_all('/< *img[^>]*src *= *["\']?([^"\']*)/is', $content, $imgMatchs);
        preg_match_all('/< *img[^>]*alt *= *["\']?([^"\']*)/is', $content, $altMatchs);
        preg_match_all('/< *a[^>]*href *= *["\']?([^"\']*)/is', $content, $linkMatchs);
        preg_match_all('/< *a[^>]*target *= *["\']?([^"\']*)/is', $content, $targetMatchs);
        if (trim($content)) {
            foreach ($data['carousel'] as $key => &$imglink) {
                $unset = true;
                if (!empty($imgMatchs[1][$key])) {
                    $unset = false;
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

                if ($unset) {
                    unset($data['carousel'][$key]);
                }
            }
        }

        $blockService->updateBlockTemplate($blockTemplate['id'], array(
            'data' => $data,
        ));
    }

    public static function updateLinks($code)
    {
        $blockService = ServiceKernel::instance()->getBiz()->service('Content:BlockService');
        $block = $blockService->getBlockTemplateByCode($code);
        $data = $block['data'];
        $content = $block['content'];
        preg_match_all('/< *dt.*?>(.*?)<\/dt>/is', $content, $textMatchs);
        preg_match_all('/< *dl.*?>.*?<\/dl>/is', $content, $dlMatchs);
        $index = 0;
        $index2 = 0;

        if (trim($content)) {
            foreach ($data as $key => &$object) {
                if (in_array($key, array('firstColumnText', 'secondColumnText', 'thirdColumnText', 'fourthColumnText', 'fifthColumnText'))) {
                    $object[0]['value'] = $textMatchs[1][$index];
                    ++$index;
                }

                if (in_array($key, array('firstColumnLinks', 'secondColumnLinks', 'thirdColumnLinks', 'fourthColumnLinks', 'fifthColumnLinks'))
                    && !empty($dlMatchs[0][$index2])) {
                    $dl = $dlMatchs[0][$index2];
                    ++$index2;
                    preg_match_all('/< *a[^>]*href *= *["\']?([^"\']*)/i', $dl, $hrefMatchs);
                    preg_match_all('/< *a[^>]*target *= *["\']?([^"\']*)/i', $dl, $targetMatchs);
                    preg_match_all('/< *a.*?>(.*?)<\/a>/i', $dl, $valuetMatchs);
                    foreach ($object as $i => &$item) {
                        $unset = true;
                        if (!empty($hrefMatchs[1][$i])) {
                            $item['href'] = $hrefMatchs[1][$i];
                        }

                        if (!empty($targetMatchs[1][$i])) {
                            $item['target'] = $targetMatchs[1][$i];
                        }

                        if (!empty($valuetMatchs[1][$i])) {
                            $unset = false;
                            $item['value'] = $valuetMatchs[1][$i];
                        }

                        if ($unset) {
                            unset($object[$i]);
                        }
                    }
                }
            }
        }

        $blockService->updateBlockTemplate($block['id'], array(
            'data' => $data,
        ));
    }
}
