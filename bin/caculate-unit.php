<?php

$i = 0;

$xmls = array();  //所有xml
while (true) {
    ++$i;

    $filePath = __DIR__.'/../reports_tmp/phpunit.xml_'.$i;
    if (file_exists($filePath)) {
        $xmls[] = simplexml_load_file($filePath);
    } else {
        break;
    }
}

if (!empty($xmls)) {
    $result = conbineUnitXml($xmls);
    $result->asXML(__DIR__.'/../reports/phpunit.xml');
}

/**
 * @param $xmls xml的格式如下
 * <?xml version="1.0" encoding="UTF-8"?>
 * <testsuites>
 *   <testsuite name="" tests="8" assertions="33" failures="0" errors="0" time="27.934177">
 *     <testsuite name="Unit1" tests="8" assertions="33" failures="0" errors="0" time="27.934177">
 *       <testsuite name="AppBundle\Common\Tests\FileTookitTest" file="/private/var/www/projects/edusoho_bak/tests/Unit/AppBundle/Common/FileToolkitTest.php" tests="8" assertions="33" failures="0" errors="0" time="27.934177">
 *         <testcase name="testGetMimeTypeByExtension" class="AppBundle\Common\Tests\FileTookitTest" file="/private/var/www/projects/edusoho_bak/tests/Unit/AppBundle/Common/FileToolkitTest.php" line="10" assertions="3" time="6.138002"/>
 *         <testcase name="testGetFileTypeByVideoExtension" class="AppBundle\Common\Tests\FileTookitTest" file="/private/var/www/projects/edusoho_bak/tests/Unit/AppBundle/Common/FileToolkitTest.php" line="22" assertions="10" time="3.084246"/>
 *         <testcase name="testGetFileTypeByAudioExtension" class="AppBundle\Common\Tests\FileTookitTest" file="/private/var/www/projects/edusoho_bak/tests/Unit/AppBundle/Common/FileToolkitTest.php" line="55" assertions="2" time="3.103115"/>
 *         <testcase name="testGetFileTypeByImageExtension" class="AppBundle\Common\Tests\FileTookitTest" file="/private/var/www/projects/edusoho_bak/tests/Unit/AppBundle/Common/FileToolkitTest.php" line="64" assertions="5" time="3.116128"/>
 *         <testcase name="testGetFileTypeByDocumentExtension" class="AppBundle\Common\Tests\FileTookitTest" file="/private/var/www/projects/edusoho_bak/tests/Unit/AppBundle/Common/FileToolkitTest.php" line="82" assertions="7" time="3.084973"/>
 *         <testcase name="testGetFileTypeByPptExtension" class="AppBundle\Common\Tests\FileTookitTest" file="/private/var/www/projects/edusoho_bak/tests/Unit/AppBundle/Common/FileToolkitTest.php" line="106" assertions="2" time="3.106500"/>
 *         <testcase name="testGetFileTypeByFlashExtension" class="AppBundle\Common\Tests\FileTookitTest" file="/private/var/www/projects/edusoho_bak/tests/Unit/AppBundle/Common/FileToolkitTest.php" line="115" assertions="1" time="3.060542"/>
 *         <testcase name="testGetFileTypeByOtherExtension" class="AppBundle\Common\Tests\FileTookitTest" file="/private/var/www/projects/edusoho_bak/tests/Unit/AppBundle/Common/FileToolkitTest.php" line="121" assertions="3" time="3.240671"/>
 *       </testsuite>
 *     </testsuite>
 *   </testsuite>
 * </testsuites>
 *
 *   每个xml的testsuite 不一样, 合并后，修改父节点的 相应的属性总数即可
 *
 * @return 组合好的xml
 */
function conbineUnitXml($xmls)
{
    $result = $xmls[0];

    list($totalInfos, $nodeInfos) = generateUnitNodeInfos($xmls);

    foreach ($result->testsuite as $totalTestsuiteNode) {
        unset($totalTestsuiteNode->testsuite);

        batchSetAttributes(
            $totalTestsuiteNode,
            $totalInfos,
            array('tests', 'assertions', 'failures', 'errors', 'time')
        );

        foreach ($nodeInfos as $testsuiteNodeInfo) {
            $testsuiteNode = $totalTestsuiteNode->addChild('testsuite');
            batchAddAttributes(
                $testsuiteNode,
                $testsuiteNodeInfo,
                array('name', 'tests', 'assertions', 'failures', 'errors', 'time')
            );

            foreach ($testsuiteNodeInfo['unitClasses'] as $unitClassNodeInfo) {
                $unitClassNode = $testsuiteNode->addChild('testsuite');
                batchAddAttributes(
                    $unitClassNode,
                    $unitClassNodeInfo,
                    array('name', 'file', 'tests', 'assertions', 'failures', 'errors', 'time')
                );

                foreach ($unitClassNodeInfo['unitMethods'] as $unitMethodNodeInfo) {
                    $unitMethodNode = $unitClassNode->addChild('testcase');
                    batchAddAttributes(
                        $unitMethodNode,
                        $unitMethodNodeInfo,
                        array('name', 'class', 'file', 'line', 'assertions', 'time')
                    );
                }
            }
        }
    }

    return $result;
}

/**
 * @param $xmls @see conbineXml
 *
 * @return
 *  array(
 *      array(  //totalInfos
 *          'tests' => {testsNum},
 *          'assertions' => {assertionsNum},
 *          'failures' => {failuresNum},
 *          'errors' => {errorsNum},
 *          'time' => {timeNum},
 *      ),
 *      array( //nodeInfos
 *          array(
 *              'name' => '{testSuiteName}',
 *              'tests' => {testsNum},
 *              'assertions' => {assertionsNum},
 *              'failures' => {failuresNum},
 *              'errors' => {errorsNum},
 *              'time' => {timeNum}',
 *              'unitClasses' => array(
 *                  array(
 *                      'name' => '{className}',
 *                      'file' => '{fileName}',
 *                      'tests' => '{testsNum}',
 *                      'assertions' => '{assertionsNum}',
 *                      'failures' => '{failuresNum}',
 *                      'errors' => '{errorsNum}',
 *                      'time' => '{time}',
 *                      'unitMethods' => array(
 *                          array(
 *                              'name' => '{methodName}',
 *                              'class' => '{className}',
 *                              'file' => '{fileName}',
 *                              'line' => '{lineNum}',
 *                              'assertions' => '{assertionsNum}',
 *                              'time' => '{timeNum}',
 *                          ),
 *                          ...
 *                      ),
 *                  ),
 *                  ...
 *              ),
 *          ),
 *          ...
 *      ),
 *  )
 */
function generateUnitNodeInfos($xmls)
{
    $nodeInfos = array();
    $totalInfos = array(
        'tests' => 0,
        'assertions' => 0,
        'failures' => 0,
        'errors' => 0,
        'time' => 0,
    );

    foreach ($xmls as $xml) {
        foreach ($xml->testsuite->testsuite as $testsuiteNode) {
            $totalInfos = increaseAttributes(
                $testsuiteNode,
                $totalInfos,
                array('tests', 'assertions', 'failures', 'errors', 'time')
            );

            $nodeInfo = getAttributesInfo($testsuiteNode);

            $nodeInfo['unitClasses'] = array();

            foreach ($testsuiteNode->testsuite as $unitClassNode) {
                $unitClassInfo = getAttributesInfo($unitClassNode);
                $unitClassInfo['unitMethods'] = array();

                foreach ($unitClassNode->testcase as $unitMethodNode) {
                    $unitMethodInfo = getAttributesInfo($unitMethodNode);
                    $unitClassInfo['unitMethods'][] = $unitMethodInfo;
                }

                $nodeInfo['unitClasses'][] = $unitClassInfo;
            }

            $nodeInfos[] = $nodeInfo;
        }
    }

    return array(
        $totalInfos,
        $nodeInfos,
    );
}

function increaseAttributes($node, $result, $fieldNames)
{
    $attrs = $node->attributes();

    foreach ($fieldNames as $fieldName) {
        $result[$fieldName] += (float) $attrs->$fieldName;
    }

    return $result;
}

function batchSetAttributes($node, $infos, $fieldNames)
{
    foreach ($fieldNames as $fieldName) {
        $node->attributes()->$fieldName = $infos[$fieldName];
    }
}

function batchAddAttributes($node, $infos, $fieldNames)
{
    foreach ($fieldNames as $fieldName) {
        $node->addAttribute($fieldName, $infos[$fieldName]);
    }
}

/**
 * 将一个节点信息返回成 一个 json
 */
function getAttributesInfo($node)
{
    $attrs = $node->attributes();

    $result = array();
    foreach ($attrs as $name => $value) {
        $result[$name] = (string) $value;
    }

    return $result;
}
