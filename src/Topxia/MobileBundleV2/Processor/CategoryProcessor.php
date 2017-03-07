<?php

namespace Topxia\MobileBundleV2\Processor;

interface CategoryProcessor
{
    public function getCategories();

    public function getAllCategories();

    public function getCategorieTree();

    public function getTags();
}
