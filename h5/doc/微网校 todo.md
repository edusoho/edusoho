## 微网校 todo

- 路由跳转规则要做，细节要讨论  @钟云昶 @高勇
- 测试未完成 @高勇
- 微网校二期 pc端配置 @高勇
- 打包方案要做，通过配置的目录（web/h5）打包，这个配置可以灵活更改 @高勇
- 打包资源的大小要控制，这个斟酌把控 done 
	- [提取公共模块：vue、vuex、vant、axios](/build/webpack.prod.conf.js)
	- 文件内容不改变，hash值不变
- 支付接口调试
	- 支付接口还有点问题 需要双方再测试下 @高勇 @沈楚彭
- 渲染单页前，要优先访问setting/wap，并且确认设备匹配， 确认h5开启后才渲染业务页面 done
	- main.js中进行判断
	
	``` 
	
	Api.getSettings({
	  query: {
	    type: 'wap'
	  }
	}).then(res => {
	  if (!res.enabled) {
	    // 如果没有开通微网校，则跳回老版本网校 TODO
	    window.location.href = axios.defaults.baseURL || 'http://zyc.st.edusoho.cn/';
	    return;
	  }
	
	  new Vue({
	    el: '#app',
	    router,
	    store,
	    components: { App },
	    template: '<App/>'
	  });
	});
	``` 


