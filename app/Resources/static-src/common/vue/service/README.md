# 前端接口规范

前端接口存于 `app/Resources/static-src/common/vue/service` 目录下

后端接口存于 `src/ApiBundle/Api/Resource` 目录下

## 规范

1. 大模块单独拆分，比如MitiClass、File、CourSet
2. 小模块合并，比如：MiltiClassAssistant、MultiClassExam 合并为MultiClass
2. 参数统一使用对象方式传入（{ query = {}, params = {}, data = {} } = {}）。query是url上的参数，params是是get请求的参数，data是非get请求的参数

## 目录结构

``` js
service
  WrongBook
    index.js
  DashBoard
    index.js
  MiltiClass
    index.js
```

## 初始化

``` js
// service/DashBoard/index.js

import { apiClient } from 'common/vue/service/api-client.js';

export default {
	searchGraphicDatum ({ query = {}, params = {}, data = {} } = {}) {
		return apiClient.get(`/api/dashboard_graphic_datum`, { params });
	},

	getRankListById ({ query = {}, params = {}, data = {} } = {}) {
		return apiClient.get(`/api/dashboard_rank_list/${query.id}`, { params });
	},
  
  createGraphicDataum ({ query = {}, params = {}, data = {} } = {}) {
    return apiClient.add(`/api/dashboard_graphic_datum`, { data });
  }
}

```

## 使用方式

``` js

import DashBoard from 'common/vue/service/DashBoard';

async searchGraphicDatum() {
  this.graphicDatas = await DashBoard.searchGraphicDatum({
    params: { title: 'xxx' }
  });
}

```

