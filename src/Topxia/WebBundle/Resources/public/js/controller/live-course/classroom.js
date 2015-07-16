define(function(require, exports, module) {

	exports.run = function() {

		$.ajax({
			url: $("#classroom-url").data("url"),
			success: function(data){
				var url = data.url;
				if(data.param) {
					url = url+"?param="+data.param;
				}
				var html = '<iframe name="classroom" src="'+url+'" style="position:absolute; left:0; top:0; height:100%; width:100%; border:0px;" scrolling="no"></iframe>';

				$("body").html(html);
			},
			error: function(){
				var html = "进入直播教室错误，请联系管理员，<a href='javascript:document.location.reload()'>重试</a>或<a href='javascript:window.close();'>关闭</a>"
				$("#classroom-url").html(html);
			}
		})
		
	}
});