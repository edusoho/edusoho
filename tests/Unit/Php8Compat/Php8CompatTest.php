<?php

namespace Tests\Unit\Php8Compat;

use AppBundle\Twig\WebExtension;
use Biz\ItemBankExercise\Service\Impl\ChapterExerciseServiceImpl;
use Biz\Org\Service\Impl\OrgServiceImpl;
use PHPUnit\Framework\TestCase;

class Php8CompatTest extends TestCase
{
    public function testGetContextValueReportsMissingKey()
    {
        $extension = (new \ReflectionClass(WebExtension::class))->newInstanceWithoutConstructor();

        try {
            $extension->getContextValue(['a' => 1, 'b' => 2], 'c');
            $this->fail('InvalidArgumentException expected');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('Key `c` is not in context with a, b', $e->getMessage());
        }
    }

    public function testGeFullOrgNameById()
    {
        $orgs = [
            1 => ['id' => 1, 'name' => 'Root'],
            2 => ['id' => 2, 'name' => 'Child', 'parentId' => 1],
        ];
        $service = $this->getMockBuilder(OrgServiceImpl::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrg'])
            ->getMock();
        $service->method('getOrg')->willReturnCallback(function ($id) use ($orgs) {
            return $orgs[$id];
        });

        $this->assertSame('Root->Child', $service->geFullOrgNameById(2));
    }

    public function testGetAssessmentDescription()
    {
        $chapters = [
            1 => ['name' => 'A', 'parent_id' => 0],
            2 => ['name' => 'B', 'parent_id' => 1],
            3 => ['name' => 'C', 'parent_id' => 2],
        ];
        $service = $this->getMockBuilder(ChapterExerciseServiceImpl::class)
            ->disableOriginalConstructor()
            ->setMethods(['getChapter'])
            ->getMock();
        $service->method('getChapter')->willReturnCallback(function ($id) use ($chapters) {
            return isset($chapters[$id]) ? $chapters[$id] : null;
        });

        $method = new \ReflectionMethod(ChapterExerciseServiceImpl::class, 'getAssessmentDescription');
        $method->setAccessible(true);

        $this->assertSame('Module > A > B > C', $method->invoke($service, 3, ['title' => 'Module']));
    }

    public function testCourseRemindNotificationJobCompiles()
    {
        list($code, $output) = $this->lint('src/Biz/WeChatNotification/Job/CourseRemindNotificationJob.php');

        $this->assertSame(0, $code, $output);
    }

    /**
     * @dataProvider nullableParameterFiles
     */
    public function testNoImplicitlyNullableParameters($file)
    {
        list(, $output) = $this->lint($file);
        preg_match_all('/^.*Implicitly marking parameter.*$/m', $output, $matches);

        $this->assertSame([], $matches[0], implode("\n", $matches[0]));
    }

    public function nullableParameterFiles()
    {
        return [
            ['src/AppBundle/Common/Exception/BaseException.php'],
            ['src/AppBundle/Common/Tree.php'],
            ['src/AppBundle/Component/Activity/ActivityRuntimeContainer.php'],
            ['src/AppBundle/Controller/BaseController.php'],
            ['src/AppBundle/DataCollector/CloudApiDataCollector.php'],
            ['src/AppBundle/Handler/LoginCaptchaAuthentication.php'],
            ['src/AppBundle/Twig/Asset/PathPackage.php'],
            ['src/Biz/BaseTestCase.php'],
            ['src/Biz/File/Service/FileImplementor.php'],
            ['src/Biz/File/Service/Impl/CloudFileImplementorImpl.php'],
            ['src/Biz/File/Service/Impl/LocalFileImplementorImpl.php'],
            ['src/Biz/File/Service/Impl/SupplierFileImplementorImpl.php'],
            ['src/Biz/File/Service/Impl/UploadFileServiceImpl.php'],
            ['src/Biz/File/Service/UploadFileService.php'],
            ['src/Biz/User/Service/Impl/MessageServiceImpl.php'],
            ['src/Biz/User/Service/Impl/UserServiceImpl.php'],
            ['src/Biz/User/Service/MessageService.php'],
            ['src/Biz/Util/FileUtil.php'],
            ['src/MarketingMallBundle/Common/GoodsContentBuilder/AbstractBuilder.php'],
        ];
    }

    private function lint($file)
    {
        $command = escapeshellarg(PHP_BINARY).' -d display_errors=1 -d error_reporting=-1 -l '
            .escapeshellarg(__DIR__.'/../../../'.$file).' 2>&1';
        exec($command, $lines, $code);

        return [$code, implode("\n", $lines)];
    }
}
