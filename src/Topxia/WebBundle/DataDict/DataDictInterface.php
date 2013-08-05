<?php
namespace Topxia\WebBundle\DataDict;

interface DataDictInterface
{
    function getDict();

    /**
     * 用于Form组件
     */
    function getGroupedDict();

    /**
     * 用于dict_text filter
     */
    function getRenderedDict();

}