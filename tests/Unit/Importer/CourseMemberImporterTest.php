<?php

namespace Tests\Unit\Importer;

use Biz\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;

class CourseMemberImporterTest extends BaseTestCase
{
    public function testImport()
    {
//        $importData = $request->request->get('importData');
//        $courseId = $request->request->get('courseId');
//        $price = $request->request->get('price');
//        $remark = $request->request->get('remark', '通过批量导入添加');
//        $course = $this->getCourseService()->getCourse($courseId);
//        $orderData = array(
//            'amount' => $price,
//            'remark' => $remark,
//        );
//
//        return $this->excelDataImporting($course, $importData, $orderData);

        $request = new Request(
            array(),
            array(
                'importData' => 1,
                'courseId' => 1,
                'price' => 1
            )
        );

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'get', 'withParams' => 1, 'returnValue' => array())
        ));

        $this->mockBiz()
    }
}