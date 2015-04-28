## Api请求说明:
- 请求支持GET,POST
#### http://hostname/mapi_v2/service/method
#### Example : http://trymob.edusoho.cn/mapi_v2/Category/getAllCategories
#### 备注:
* hostname  域名
* service   服务接口名
* method    接口方法


## Api服务列表

* [Category](#Category)
* Course
* Lesson
* User
* Order
* School
* Testpaper

<a id="Category" />
### Category 接口方法列表

* getCategories *获取指定分类*

```
  参数          类型            描述
  category      String          父级分类名称
  
  返回
  json
```
    
* getAllCategories  *获取所有分类*

```
  返回
  json
```
* getTags   *获取所有标签*

```
  最大返回100条
  返回
  json
```

<a id="Course" />
### Course 接口方法列表