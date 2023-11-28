<?php

namespace Biz\Question\Traits;

trait QuestionFormulaImgTrait
{
    private function convertFormulaToImg($question)
    {
        $question = preg_replace_callback('/<span( data-display)?([^>]*?) data-tex=\\\\"(.*?)\\\\"( data-display)? data-img=\\\\"(.*?)\\\\"><\\\\\/span>/', function ($match) {
            return "<img src=\\\"$match[5]\\\">";
        }, json_encode($question));

        return json_decode($question, true);
    }

    private function addEmphasisStyle($text)
    {
        return preg_replace_callback('/data-emphasis/', function () {
            return 'style=\"-webkit-text-emphasis-style:\'ꔷ\';-webkit-text-emphasis-position:under;\" data-emphasis';
        }, $text);
    }

    private function addItemEmphasisStyle($item)
    {
        $text = $this->addEmphasisStyle(json_encode($item));

        return json_decode($text, true);
    }
}
