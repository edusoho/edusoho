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
        preg_match_all('/<img.*? src=\\\\"(.*?)\\\\"/', $text, $matches);
        $imgs = $matches[1] ?? [];
        if (empty($imgs)) {
            return $text;
        }
        $localImgs = $this->downloadRemoteImgToLocal($imgs);
        $replaceImgs = array_combine($imgs, $this->convertImgUri(array_column($localImgs, 'uri')));

        return preg_replace_callback(
            '/<img.*? src=\\\\"(.*?)\\\\"/',
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
            $localPath = $this->container->getParameter('topxia.upload.public_directory').'/'.Uuid::uuid4().'.'.$match[3];
            file_put_contents($localPath, file_get_contents($img));
            $localImgs[] = $localPath;
        }

        return $this->getFileService()->addFiles('question', $localImgs);
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
        preg_match_all('/data-tex=\\\\"([^"]*)\\\\"/', $text, $matches);
        $formulas = $matches[1] ?? [];
        if (empty($formulas)) {
            return $text;
        }
        foreach ($formulas as &$formula) {
            $formula = html_entity_decode($formula, ENT_QUOTES);
        }
        $unescapeFormulas = str_replace('\\\\', '\\', $formulas);
        $replaceImgs = array_combine($formulas, $this->convertFormulaToImg($unescapeFormulas));
        $replaceFunc = function ($match) use ($replaceImgs) {
            $tex = str_replace('\\\ ', '', $match[3]);

            return "<span$match[1] data-tex=\\\"$tex\\\"$match[4] data-img=\\\"{$replaceImgs[html_entity_decode($match[3], ENT_QUOTES)]}\\\"></span>";
        };

        return preg_replace_callback('/<span( data-display)?([^>]*?) data-tex=\\\\"(.*?)\\\\"( data-display)?(.*?)><\/span>/', $replaceFunc, $text);
    }

    private function convertFormulaToImg(array $formulas)
    {
        $convertedFormulas = $this->getQuestionService()->findQuestionFormulaImgRecordsByFormulas($formulas);
        $needConvertFormulas = array_values(array_unique(array_diff($formulas, array_column($convertedFormulas, 'formula'))));
        if ($needConvertFormulas) {
            $this->downloadFormulaImgToLocal($needConvertFormulas);
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
        $localImgs = $this->downloadRemoteImgToLocal($imgs);
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

    private function addEmphasisStyle($text)
    {
        return preg_replace_callback('/data-emphasis/', function () {
            return 'style=\"-webkit-text-emphasis-style:\'ꔷ\';-webkit-text-emphasis-position:under;\" data-emphasis';
        }, $text);
    }

    private function addArrayEmphasisStyle($array)
    {
        $text = $this->addEmphasisStyle(json_encode($array));

        return json_decode($text, true);
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
