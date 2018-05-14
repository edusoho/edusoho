<?php

$i = 0;

$result = null;   //最后一份xml
$xmls = array();  //除第一份xml外的其他xml
while (true) {
    ++$i;

    $filePath = __DIR__.'/../reports_tmp/phpunit.coverage.xml_'.$i;
    if (file_exists($filePath)) {
        $xmlInfo = simplexml_load_file($filePath);
        $result = $xmlInfo;
        $xmls[] = $xmlInfo;
    } else {
        break;
    }
}

/**
 * @param $xmls xml的格式如下
 *    <?xml version="1.0" encoding="UTF-8"?>
 *    <coverage generated="1525869102">
 *      <project timestamp="1525869102">
 *        <package name="AppBundle">
 *          <file name="/private/var/www/projects/edusoho_bak/src/AppBundle/AppBundle.php">
 *            <class name="AppBundle" namespace="AppBundle">
 *              <metrics methods="2" coveredmethods="0" conditionals="0" coveredconditionals="0" statements="3" coveredstatements="0" elements="5" coveredelements="0"/>
 *            </class>
 *            <line num="11" type="method" name="build" crap="2" count="0"/>
 *            <line num="13" type="stmt" count="0"/>
 *            <line num="14" type="stmt" count="0"/>
 *            <line num="16" type="method" name="getEnabledExtensions" crap="2" count="0"/>
 *            <line num="18" type="stmt" count="0"/>
 *            <metrics loc="20" ncloc="20" classes="1" methods="2" coveredmethods="0" conditionals="0" coveredconditionals="0" statements="3" coveredstatements="0" elements="5" coveredelements="0"/>
 *          </file>
 *        </package>
 *      </project>
 *    </coverage>
 *
 * @return
 *  array(
 *      '{fileName} => array(   // fileName 为 <file 节点中的 name
 *          'num_{num}' => array(
 *              'type' => 'method',  //类型，只有 method 和 stmt 2种, num, type, count, 对应到 line 中的相应属性
 *              'count' => 1  //所有coverage的count总和
 *              'num' => {num}
 *          )
 *      )
 *  )
 */
$nodeInfos = array();

foreach ($xmls as $xml) {
    foreach ($xml->project->package as $packageNode) {
        foreach ($packageNode->file as $fileNode) {
            $fileName = (string) $fileNode->attributes()->name;

            if (empty($nodeInfos[$fileName])) {
                $nodeInfos[$fileName] = array();
            }

            foreach ($fileNode->line as $lineNode) {
                $lineNodeAttrs = $lineNode->attributes();
                $lineNum = (string) $lineNodeAttrs->num;
                $type = (string) $lineNodeAttrs->type;
                $count = (int) $lineNodeAttrs->count;

                if (!isset($nodeInfos[$fileName]['num_'.$lineNum])) {
                    $info = array(
                        'type' => $type,
                        'count' => 0,
                        'num' => $lineNum,
                        'existedCount' => 0,
                    );

                    if ('method' == $type) {
                        $info['name'] = (string) $lineNodeAttrs->name;
                        $info['crap'] = (string) $lineNodeAttrs->crap;
                    }

                    $nodeInfos[$fileName]['num_'.$lineNum] = $info;
                }

                $nodeInfos[$fileName]['num_'.$lineNum]['count'] += $count;
                $nodeInfos[$fileName]['num_'.$lineNum]['existedCount'] += 1;
            }
        }
    }
}

// 执行单元测试时，才会判断 某些行是否为 有效代码行（如 左大花括号{ 独立一行，会被认为非代码行），
//  分批执行单元测试，有些批次由于没有执行到相应的代码，不会过滤掉非代码行，这些代码行需要过滤
//  即，existedCount != xmls 数量时，直接忽略
$validExistedCount = count($xmls);

foreach ($result->project->package as $packageNode) {
    foreach ($packageNode->file as $fileNode) {
        $fileName = (string) $fileNode->attributes()->name;
        unset($fileNode->line);

        $methodCount = 0;
        $coveredMethodsCount = 0;
        $statementsCount = 0;
        $coveredStatementsCount = 0;

        foreach ($nodeInfos[$fileName] as $key => $attrs) {
            if ($validExistedCount != $attrs['existedCount']) {
                continue;
            }

            unset($attrs['existedCount']);
            $lineNode = $fileNode->addChild('line');

            if ('method' == $attrs['type']) {
                ++$methodCount;
                if (!empty($attrs['count'])) {
                    ++$coveredMethodsCount;
                }
            }

            if ('stmt' == $attrs['type']) {
                ++$statementsCount;
                if (!empty($attrs['count'])) {
                    ++$coveredStatementsCount;
                }
            }

            foreach ($attrs as $key => $value) {
                $lineNode->addAttribute($key, $value);
            }
        }

        $metric = $fileNode->class->metrics[0];
        $metric->attributes()->methods = $methodCount;
        $metric->attributes()->coveredmethods = $coveredMethodsCount;
        $metric->attributes()->statements = $statementsCount;
        $metric->attributes()->coveredstatements = $coveredStatementsCount;
    }
}

$result->asXML(__DIR__.'/../reports/phpunit.coverage.xml');
