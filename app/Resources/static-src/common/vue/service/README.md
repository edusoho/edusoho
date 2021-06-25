# 前端接口规范

前端接口存于 `app/Resources/static-src/common/vue/service` 目录下

后端接口存于 `src/ApiBundle/Api/Resource` 目录下

## 规范

1. 文件名、层级结构要与后端接口对应。
2. 参数统一使用对象方式传入（params）。

## 目录结构（以 WrongBook 为例子）

``` js
service
  WrongBook
    index.js
    WrongBookQuestionShow.js
    XxxxXxxx.js
  index.js
```

## 初始化

``` js
// service/WrongBook/WrongBookQuestionShow.js

import { apiClient } from 'common/vue/service/api-client';

const baseUrl = '/api/wrong_book';

export const WrongBookQuestionShow = {
  // 课程、班级、题库练习错题展示
  async search(params) {
    return apiClient.get(`${baseUrl}/${params.id}/question_show`, { params });
  }
}
```

``` js
// service/WrongBook/index.js

export * from './WrongBookQuestionShow';
```

``` js
// service/index.js

export * from './WrongBook';
```
