define(function(require, exports, module) {

    exports.run = function() {

        var intervalId = 0;
        var tryCount = 1;

        function getRoomUrl(){
            if(tryCount>10) {
                clearInterval(intervalId);
                var html = "进入直播教室错误，请联系管理员，<a href='javascript:document.location.reload()'>重试</a>或<a href='javascript:window.close();'>关闭</a>"
                $("#entry").html(html);
                return;
            }
            $.ajax({
                url: $("#entry").data("url"),
                success: function(data){
                    if(data.error){
                        clearInterval(intervalId);
                        var html = data.error+"，<a href='javascript:document.location.reload()'>重试</a>或<a href='javascript:window.close();'>关闭</a>"
                        $("#entry").html(html);
                        return;
                    }

                    if(data.roomUrl) {
                        clearInterval(intervalId);
                        // window.location.href = data.roomUrl;
                        var html = '<iframe name="classroom" src="'+data.roomUrl+'" style="position:absolute; left:0; top:0; height:100%; width:100%; border:0px;" scrolling="no"></iframe>';
                        $("body").html(html);
                    }
                    tryCount++;
                },
                error: function(){
                    //var html = "进入直播教室错误，请联系管理员，<a href='javascript:document.location.reload()'>重试</a>或<a href='javascript:window.close();'>关闭</a>"
                    //$("#entry").html(html);
                }
            })
        }

        getRoomUrl();

        intervalId = setInterval(getRoomUrl, 3000);
        
    }
});