<?php

namespace Codeages\Biz\ItemBank;

/**
 * 业务错误码
 * 题目相关从001开始
 * 试卷相关从101开始
 * 答题相关从201开始
 * 题库相关从301开始
 */
class ErrorCode
{
    /**
     * 答题场次不存在
     */
    const ANSWER_SCENE_NOTFOUD = 40495201;

    /**
     * 答题场次未开始
     */
    const ANSWER_SCENE_NOTSTART = 50095202;

    /**
     * 答题记录不存在
     */
    const ANSWER_RECORD_NOTFOUND = 40495203;

    /**
     * 答题未在进行中
     */
    const ANSWER_NODOING = 50095204;

    /**
     * 答题未在暂停状态
     */
    const ANSWER_NOTPAUSED = 50095205;

    /**
     * 答题报告不存在
     */
    const ANSWER_REPORT_NOTFOUND = 40495206;

    /**
     * 答题不能再次开始
     */
    const ANSWER_SCENE_CANNOT_RESTART = 50095207;

    /**
     * 答题记录无法批阅
     */
    const ANSWER_RECORD_CANNOT_REVIEW = 50095208;

    const ITEM_NOT_FOUND = 40495001;

    const ITEM_ARGUMENT_INVALID = 50095002;

    const QUESTION_ARGUMENT_INVALID = 50085003;

    const ITEM_CATEGORY_NOT_FOUND = 40495004;

    const ITEM_NOT_ENOUGH = 50095005;

    const ANSWER_MODE_NOTFOUND = 50095006;

    /**
     * 题目附件不存在
     */
    const ITEM_ATTACHMENT_NOTFOUND = 50095007;

    const QUESTION_NOT_FOUND = 40495008;

    /**
     * 试卷不存在
     */
    const ASSESSMENT_NOTFOUND = 40495101;

    /**
     * 试卷状态错误
     */
    const ASSESSMENT_STATUS_ERROR = 50095102;

    /**
     * 试卷暂未发布
     */
    const ASSESSMENT_NOTOPEN = 50095103;

    /**
     * 题库不存在
     */
    const ITEM_BANK_NOT_FOUND = 40495301;

    const ITEM_BANK_NOT_EMPTY = 40395302;
}
