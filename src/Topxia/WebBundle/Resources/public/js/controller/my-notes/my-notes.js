define(function(require, exports, module) {

        require('ckeditor');
        var Notify = require('common/bootstrap-notify');
        exports.run = function() {

            $(".operations").on('click', ".note-delete", function() {
                var $btn = $(this);
                $.post($btn.data('url'), function(response) {

                    if(response.status == "ok"){
                        $btn.parent().parent().next().find(".operations").show();
                        $btn.parent().parent().remove();
                        $('#' + 'note-li-'+ response.id).next().addClass("active");
                        $('#' + 'note-li-'+ response.id).remove();
                    }

                },'json');
            });

            $(".operations").on('click', ".note-edit", function() {
                var $btn = $(this);
                $btn.parent().parent().show();
                $btn.parent().next('button[type=submit]').show();
                $btn.parent().find(".note-content").find("p").remove();

                var selectorId = $btn.parent().find(".note-content").find("textarea").attr("id");
                CKEDITOR.replace(selectorId, {
                    resize_enabled: false,
                    forcePasteAsPlainText: true,
                    toolbar: 'Simple',
                    removePlugins: 'elementspath',
                    filebrowserUploadUrl: '/ckeditor/upload?group=course'
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

            $(".note-list").on('click', '[data-role=note-in-list]', function(){
                var li = $(this);
                $(".note-list").find('[data-role=note-in-list]').each(function(index, item){
                    $(this).removeClass("active");
                });
                $(this).addClass("active");
                var noteId = $(this).attr("value");
                $(".course-notes").find(".note-form").hide();

                $(".course-notes").find(".note-form").each(function(index, item){
                    if(noteId == $(this).attr("noteId")){
                        $(this).show().find(".operations").show();
                    }
                });

            });

        };

});