webpackJsonp(["app/js/live-course/classroom/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var intervalId = 0;
	var tryCount = 1;
	
	function getRoomUrl() {
	  if (tryCount > 10) {
	    clearInterval(intervalId);
	
	    var html = Translator.trans('进入直播教室错误，请联系管理员，') + "<a href='javascript:document.location.reload()'>" + Translator.trans('重试') + "</a>" + Translator.trans('或') + "<a href='javascript:window.close();'>" + Translator.trans('关闭') + "</a>";
	
	    $("#classroom-url").html(html);
	    return;
	  }
	  $.ajax({
	    url: $("#classroom-url").data("url"),
	    success: function success(data) {
	      if (data.error) {
	        clearInterval(intervalId);
	
	        var _html = data.error + Translator.trans('，') + "<a href='javascript:document.location.reload()'>" + Translator.trans('重试') + "</a>" + Translator.trans('或') + "<a href='javascript:window.close();'>" + Translator.trans('关闭') + "</a>";
	
	        $("#classroom-url").html(_html);
	        return;
	      }
	
	      if (data.url) {
	        var url = data.url;
	        if (data.param) {
	          url = url + "?param=" + data.param;
	        }
	        var _html2 = '<iframe name="classroom" src="' + url + '" style="position:absolute; left:0; top:0; height:100%; width:100%; border:0px;" scrolling="no"></iframe>';
	
	        $("body").html(_html2);
	
	        clearInterval(intervalId);
	      }
	
	      tryCount++;
	    },
	    error: function error() {
	      //var html = "进入直播教室错误，请联系管理员，<a href='javascript:document.location.reload()'>重试</a>或<a href='javascript:window.close();'>关闭</a>"
	      //$("#classroom-url").html(html);
	    }
	  });
	}
	
	getRoomUrl();
	
	intervalId = setInterval(getRoomUrl, 3000);

/***/ })
]);
//# sourceMappingURL=index.js.map