# 基于es-lint的前端编码风格规范
为避免低级 Bug、产出风格统一的代码，所以在ES中引入了es-lint以及继承了airbnb的配置。

### 启动命令

```
npm run lint
```

如果有改动JS，在每次代码push前，请执行这条命令


### 配置文件说明(根目录下.eslintrc)
- 其中rules项代表规则，值为0，表示不需要遵守；值为1，表示警告，待修复；值为2，表示错误，需要修复的
- 其中globals项里包含了全局变量，more不允许使用全局变量
- 其中extends项表示继承了airbnb的基础规则
- 其中env项表示支持的环境


### 检测范围
现将app/Resources/static-src下的js，纳入到了代码风格检测工具的检测范围里


### 使用说明
可以对于一些js文件，如第三方库，不想检测编码风格的，可以如下操作：

```
/* eslint-disable */

....具体的代码

/* eslint-enable */
```

也可以指定具体不验证的规范:

```
/*eslint-disable no-alert, no-console */
alert('foo');

console.log('bar');

/*eslint-enable no-alert */
```


### 常见错误(需要大家不断补充)
以下罗列的是在启动编码风格检测工具时，出现的错误。

* 报错信息统一说明：

```
xxx/app/Resources/static-src/common/load-animation.js
6:16  error  Irregular whitespace not allowed  no-irregular-whitespace

xxx/app/Resources/static-src/common/load-animation.js 代表错误所在的js文件

6:16 代表在第6行第16个字符
errer 代表报错信息的级别，也可能是warning
Irregular whitespace 对于报错信息的简要说明
no-irregular-whitespace 对应es-lint中的配置项
```

* 其他说明

文件结尾需要空且只空1行 <br>

* 最好用"."

```
["script"] is better written in dot notation     dot-notation

如：
window['script'] = script;

改成：
window.script = script;
```

* 另起一行时，上一行的结尾不要留空格

```
Trailing spaces not allowed   no-trailing-spaces
```

* 运算符前后都需要留空格

```
Infix operators must be spaced space-infix-ops

如：
fileSizeLimit: 2*1024*1024

改为：
fileSizeLimit: 2 * 1024 * 1024
```

* 缺少默认的case

```
Expected a default case    default-case

如：
switch(type) {
  case 1:
    break;
  case 2:
    break;
}

改为：
switch(type) {
  case 1:
    break;
  case 2:
    break;
  default:
    break;
}
```

* 空格不符合规范

```
Irregular whitespace not allowed  no-irregular-whitespace

错误原因：
1.js现统一采用2个空格（spaces），报错的代码中的空格数可能是其它值
2.添加了一些多余的空格
```

* 用const 替代 let

```
Exporting mutable 'let' binding, use 'const' instead   import/no-mutable-exports
```

* function函数名后面留一个空格

```
Missing space before function parentheses   space-before-function-paren
```

* 在注释"//"后面留一个空格

```
Expected exception block, space or tab after '//' in comment     spaced-comment
```

* 字符串必须用单引号

```
Strings must use singlequote  quotes
```

* 用箭头方式代替函数表达式

```
Unexpected function expression   prefer-arrow-callback

如：
$("node").on("click", function() {

})

改为：
$('node').on('click', () => {

})
```

* 代码缩进不对

```
Expected indentation of 6 spaces but found 8   indent

Expected indentation of 2 spaces but found 1 tab  indent
```

* 太多空行

```
Too many blank lines at the end of file. Max of 1 allowed   no-multiple-empty-lines
```

* 缺少分号

```
Missing semicolon   semi

如：
import { isMobileDevice } from 'common/utils'

后面需加上分号
```

* 不要用多重赋值

```
Unexpected chained assignment    no-multi-assign

如：
let $picture = this.element = $(this.config.element);

改为：
this.element = $(this.config.element);
let $picture = this.element;
```

* 绝对路径的import依赖应当先于相对路径的import依赖引入

```
Absolute imports should come before relative imports   import/first

如：
import WebUploader from './fex-webuploader/webuploader.js';
import SWF_PATH from './fex-webuploader/Uploader.swf';
import notify from 'common/notify';

改为：
import notify from 'common/notify';
import WebUploader from './fex-webuploader/webuploader.js';
import SWF_PATH from './fex-webuploader/Uploader.swf';
```

* 左括号的位置

```
Opening curly brace does not appear on the same line as controlling statement  brace-style

如： 
class EsWebUploader 
{

}

改为： 
class EsWebUploader {

}
```

* 方法简写

```
Expected method shorthand   object-shorthand

如：

let a = {
  b: b,
  c: function() {
    ...
  }
}

改成

let a = {
  b,
  c() {
    ...
  }
}
```

* 无用的构造函数

```
Useless constructor  no-useless-constructor

如：
export default class EsEmitter extends Emitter {
  constructor() {
    super();
  }
}
```

* 不需要的引号

```
Unnecessarily quoted property '_csrf_token' found  quote-props

如：
let a = {
  '_csrf_token': 1
}

改为：
let a = {
  _csrf_token: 1
}
```

* 不需要的空格

```
There should be no spaces inside this paren  space-in-parens

如：
uploader.on( 'fileQueued', function( file ) {

})

改为：

uploader.on( 'fileQueued', (file) => {

})
```

* 不能对函数参数重新赋值

```
Assignment to function parameter 'time'   no-param-reassign

如：
delay(event, cb, time) {
  time = time || 0;
}

改为：
delay(event, cb, time = 0) {
  
}

```

* 不应该空行

```
Block must not be padded by blank lines   padded-blocks

如： 
delay(event, cb, time = 0) {

  let delayCb = function () {}
}

改为：
delay(event, cb, time = 0) {
  let delayCb = function () {}
}
```

* 在','后应该添加一个空格

```
A space is required after ','     comma-spacing
```

* 混淆了spaces 和 tabs

```
Mixed spaces and tabs  no-mixed-spaces-and-tabs
```

* 请用 '===' 取代 '=='

```
Expected '===' and instead saw '=='  eqeqeq
```

* 重复定义

```
'a' is already defined  no-redeclare
```

* 期望看到一个表达式，而不是赋值

```
Expected a conditional expression and instead saw an assignment  no-cond-assign

如：
if (a = b) {}

改成：
if (a === b) {}
```