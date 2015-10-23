define(function(require, exports, module) {
    
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $('#message-reply-form').on('click', '#course-reply-btn', function(e){
            $("#course-reply-btn").addClass("disabled");
            if($("#message_reply_content").val().length >= 500){
                Notify.danger("不好意思，私信内容长度不能超过500!");
                return false;
            }

            if($.trim($("#message_reply_content").val()).length == 0){
                Notify.danger("不好意思，私信内容不允许为空!");
                return false;
            }

            $.post($("#message-reply-form").attr('action'), $("#message-reply-form").serialize(), function(response) {
                $(".message-list").prepend(response.html);
                $("#message_reply_content").val("");
            });

            return false;
        });

        $('.message-list').on('click', '.delete-message', function(e){

            if( $(".message-list").find(".message-me").length  == 1){
                if (!confirm('本条信息为最后一条，真的要删除该私信吗？')) {
                    return false;
                }
            } else {
                if (!confirm('真的要删除该私信吗？')) {
                    return false;
                }
            }

            var $item = $(this).parents('.media');
            $.post($(this).data('url'), function(){
                if($(".message-list").find(".message-me").length  == 1){
                    window.location.href = $item.attr("parent-url");
                }
                $item.remove();
            });

        });


        $('textarea').bind('input propertychange', function() {
            if($("#message_reply_content").val().length > 0){
                $("#course-reply-btn").removeClass("disabled");
            } else {
                $("#course-reply-btn").addClass("disabled");
            }

        });

    };

});