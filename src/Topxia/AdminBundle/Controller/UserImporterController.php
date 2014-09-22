<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use Topxia\Common\SimpleValidator;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserImporterController extends BaseController
{   
    public function checkUserAction(Request $request, $type)
    {
        if ($request->getMethod() == 'POST') {
            $file = $request->files->get('excel');
            $rule = $request->request->get("rule");
            $classId = $request->request->get('classId');

            if(!is_object($file)){
                $this->setFlashMessage('danger', '请选择上传的文件');
                return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.html.twig', array(
                    'type' => $type, 
                ));
            }
            if (FileToolkit::validateFileExtension($file,'xls xlsx')) {
                $this->setFlashMessage('danger', 'Excel格式不正确！');
                return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.html.twig', array(
                    'type' => $type, 
                ));
            }

            $result = $this->getUserImportServiceByType($type)->checkUserData($file, $rule, $classId);
            if($result['status'] == 'failed') {
                if($result['type'] == 'lack_fields') {
                    $this->setFlashMessage('danger', '缺少必要的字段:' . implode(",",$result['message']));
                    return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.html.twig', array(
                        'type' => $type, 
                    ));
                } else if($result['type'] == 'over_line_limit') {
                    $this->setFlashMessage('danger', $result['message']);
                    return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.html.twig', array(
                        'type' => $type, 
                    ));
                }

            } else {
                return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.step2.html.twig', array(
                    'userCount' => count($result['allStuentData']),
                    'errorInfo'=> $result['errorInfos'],
                    'checkInfo'=> $result['checkInfo'],
                    'allUserData'=> serialize($result['allStuentData']),
                    'checkType' => $rule,
                    'classId' => $classId,
                    'type' => $type, 
                ));
            }

        }

        return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.html.twig', array(
            'type' => $type, 
        ));
    }

    public function importUserAction(Request $request, $type)
    {
        $users=$request->request->get("data");
        $classId=$request->request->get("classId");
        $users=unserialize($users);
        $checkType=$request->request->get("checkType");

        if($checkType=="ignore"){
            $this->getUserImportServiceByType($type)->importUserByIgnore($users, $classId);
        }
        if($checkType=="update"){
            $this->getUserImportServiceByType($type)->importUserByUpdate($users, $classId); 
        }
        return $this->render('TopxiaAdminBundle:UserImporter:userinfo.excel.step3.html.twig', array(
            'type' => $type,            
        ));
    }

    protected function getUserImportServiceByType($type)
    {
        if($type == 'student') {
            return $this->getServiceKernel()->createService('UserImporter.StudentImporterService'); 
        } elseif ($type == 'teacher') {
            return $this->getServiceKernel()->createService('UserImporter.TeacherImporterService');
        } else if($type == 'parent') {
            return $this->getServiceKernel()->createService('UserImporter.ParentImporterService');
        }
    }
}
