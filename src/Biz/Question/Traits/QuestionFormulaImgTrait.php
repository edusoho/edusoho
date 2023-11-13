<?php

namespace Biz\Question\Traits;

trait QuestionFormulaImgTrait
{
    private function convertFormulaToImg($question)
    {
        $question = preg_replace_callback('/<span( data-display)?(.*?) data-tex=\\\\"(.*?)\\\\"( data-display)? data-img=\\\\"(.*?)\\\\"><\\\\\/span>/', function ($match) {
            return "<img src=\\\"$match[5]\\\">";
        }, json_encode($question));

        return json_decode($question, true);
    }
}
