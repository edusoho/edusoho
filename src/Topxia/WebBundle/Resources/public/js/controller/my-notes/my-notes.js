define(function(require, exports, module) {

        require('ckeditor');
        var Notify = require('common/bootstrap-notify');
        exports.run = function() {

            $(".course-note").on('click', ".note-delete", function() {
                var $btn = $(this);
                $.post($btn.data('url'), function(response) {

                    if(response.status == "ok"){
                        $btn.parent().parent().attr('status', 'nodefined');
                        var html = '不好意思，本课时你还没有记过笔记哦！<br> <a href="'+response.url +'">快到课时页面来添加笔记吧！ </a>';
                        $btn.parent().replaceWith(html);
                        $(".content-count-"+response.lessonId).remove();
                    }

                },'json');
            });

            $(".course-note").on('click', ".note-edit", function() {
                var $btn = $(this);
                $btn.parent().find('button[type=submit]').show();
                $btn.parent().find(".note-content").find("p").remove();

                var selectorId = $btn.parent().find(".note-content").find("textarea").attr("id");
                CKEDITOR.replace(selectorId, {
                    toolbar: 'Mini',
                    removePlugins: 'elementspath'
                });

            });

            $(".note-form").on('submit', function(){
                $form = $(this);
                var selectorId = $(this).find("textarea").attr("id");
                CKEDITOR.instances[selectorId].updateElement();
                $.post($(this).attr('action'), $(this).serialize(),function(response){
                    if(response.status == "ok"){
                        Notify.success('笔记保存成功!');
                        window.location.reload();
                    } else {
                        Notify.danger('笔记保存失败!');
                    }
                }, 'json');
                
                return false;
            });

            $(".note-list").on('click', '[data-role=lesson-header-in-list]', function(){
                var li = $(this);

                $(".note-list").find('[data-role=lesson-header-in-list]').each(function(index, item){
                    $(this).removeClass("active");
                });
                $(this).addClass("active");

                $(".course-notes").find(".course-note").hide();
                var lessonId = li.attr("value");

                $(".course-notes").find(".course-note").each(function(index, item){
                    if(lessonId == $(this).attr("lessonid")){
                        $(this).show();
                    }
                });
                
            });

        };

});