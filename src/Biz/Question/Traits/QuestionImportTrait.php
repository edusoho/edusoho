<?php

namespace Biz\Question\Traits;

use Biz\Content\Service\FileService;
use Biz\Question\Adapter\QuestionParseAdapter;
use Biz\Question\QuestionParseClient;
use Biz\Question\Service\QuestionService;
use Ramsey\Uuid\Uuid;

trait QuestionImportTrait
{
    private function replaceRemoteImgToLocalImg($text)
    {
        preg_match_all('/<img src=\\\\"(.*?)\\\\"/', $text, $matches);
        $imgs = $matches[1] ?? [];
        if (empty($imgs)) {
            return $text;
        }
        $localImgs = $this->getFileService()->addFiles('course', $this->downloadRemoteImgToLocal($imgs));
        $replaceImgs = array_combine($imgs, $this->convertImgUri(array_column($localImgs, 'uri')));

        return preg_replace_callback(
            '/<img src=\\\\"(.*?)\\\\"/',
            function ($match) use ($replaceImgs) {
                return "<img src=\\\"{$replaceImgs[$match[1]]}\\\"";
            },
            $text
        );
    }

    private function downloadRemoteImgToLocal($imgs)
    {
        $localImgs = [];
        foreach ($imgs as $img) {
            preg_match('/https?:\/\/(.*?)\/(.*?)\.(.*)/', $img, $match);
            $localPath = $this->container->getParameter('topxia.upload.public_directory').'/tmp/'.Uuid::uuid4().'.'.$match[3];
            file_put_contents($localPath, file_get_contents($img));
            $localImgs[] = $localPath;
        }

        return $localImgs;
    }

    private function convertImgUri(array $uris)
    {
        $webExtension = $this->get('web.twig.extension');

        return array_map(function ($uri) use ($webExtension) {
            return $webExtension->getFpath($uri);
        }, $uris);
    }

    private function replaceFormulaToLocalImg($text)
    {
        preg_match_all('/data-tex=\\\\"([^"]*)\\\\"/', html_entity_decode($text), $matches);
        $formulas = $matches[1] ?? [];
        if (empty($formulas)) {
            return $text;
        }
        $unescapeFormulas = str_replace('\\\\', '\\', $formulas);
        $replaceImgs = array_combine($formulas, $this->convertFormulaToImg($unescapeFormulas));
        $replaceFunc = function ($match) use ($replaceImgs) {
            return "<span data-tex=\\\"$match[1]\\\"$match[2] data-img=\\\"{$replaceImgs[html_entity_decode($match[1])]}\\\"></span>";
        };

        return preg_replace_callback('/<span data-tex=\\\\"(.*?)\\\\"(.*?)><\/span>/', $replaceFunc, $text);
    }

    private function convertFormulaToImg(array $formulas)
    {
        $convertedFormulas = $this->getQuestionService()->findQuestionFormulaImgRecordsByFormulas($formulas);
        $toConvertFormulas = array_diff($formulas, array_column($convertedFormulas, 'formula'));
        if ($toConvertFormulas) {
            $this->downloadFormulaImgToLocal($toConvertFormulas);
            $convertedFormulas = $this->getQuestionService()->findQuestionFormulaImgRecordsByFormulas($formulas);
        }

        return $this->convertImgUri(array_column($convertedFormulas, 'img'));
    }

    private function downloadFormulaImgToLocal(array $formulas)
    {
        $imgs = [];
        foreach (array_chunk($formulas, 100) as $formulaChunk) {
            $imgChunk = $this->getQuestionParseClient()->convertLatex2Img($formulaChunk);
            $imgs = array_merge($imgs, $imgChunk);
        }
        $localImgs = $this->getFileService()->addFiles('course', $this->downloadRemoteImgToLocal($imgs));
        $records = [];
        foreach ($formulas as $key => $formula) {
            $records[] = [
                'formula' => $formula,
                'formula_hash' => md5($formula),
                'img' => $localImgs[$key]['uri'],
            ];
        }
        $this->getQuestionService()->createQuestionFormulaImgRecords($records);
    }

    protected function getQuestionParseClient()
    {
        return new QuestionParseClient();
    }

    protected function getQuestionParseAdapter()
    {
        return new QuestionParseAdapter();
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }
}
