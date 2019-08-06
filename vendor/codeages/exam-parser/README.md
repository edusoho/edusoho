# ExamParser

## 安装

```shell
composer require codeages/exam-parser
```

## 使用说明

* 解析题目
```php
//解析题目
$parser = new Parser($filePath);
$qustions = $parser->parser();
```

