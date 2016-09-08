 ## 微信分享自定义内容
 	
 	在需要引入的页面 进入下面页面

```
	 {% include 'TopxiaWebBundle:Common:weixin-share.html.twig' with {
	  'title': course.title,
	  'desc': course.about|striptags|purify_and_trim_html,
	  'link': app.request.uri,
	  'imgUrl': fileurl(course.largePicture,'course.png'),
	  }%}
```
