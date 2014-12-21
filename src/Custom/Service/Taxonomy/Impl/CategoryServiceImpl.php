<?php
namespace Custom\Service\Taxonomy\Impl;

use Custom\Service\Taxonomy\CategoryService;
use Topxia\Service\Taxonomy\Impl\CategoryServiceImpl as BaseCategoryServiceImpl;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class CategoryServiceImpl extends BaseCategoryServiceImpl implements CategoryService
{
    public function updateCategoryIsSearch($id, $isSearch){
        $this->getCustomCategoryDao()->updateCategoryIsSearch($id, $isSearch);
    }

    private function getCustomCategoryDao()
    {
        return $this->createDao('Custom:Taxonomy.CategoryDao');
    }



}  



