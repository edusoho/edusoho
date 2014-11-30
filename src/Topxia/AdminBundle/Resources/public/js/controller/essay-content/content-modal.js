define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() { 
        $('.method-form-group').on('change',function(){
            if ($('.title-form-group').hasClass('hide')){
                $('.tagIds-form-group').addClass('hide');
                $('.title-form-group').removeClass('hide');
            } else {
                $('.tagIds-form-group').removeClass('hide');
                $('.title-form-group').addClass('hide');
            }
        });

        $('[data-role=batch-select]').click(function(){
            if ($(this).is(":checked") == true){
                $('[data-role=single-select]').prop('checked', true);
            } else {
               $('[data-role=single-select]').prop('checked', false);
            }
        });

        $('#article-material-search').on('click',function(){
            $.get($('#article-material-search').data('url'), $('#message-search-form').serialize(), function(html) {
                $('#modal').html(html);
            });
            return false;
        });

        $('#essay-content-creat-btn').on('click',function(){
            var ids = [];
            var chapterId = $(this).data('id');
            $('[data-role=single-select]:checked').each(function(index,item) {
                ids.push($(item).data('articleMaterialId'));
            });

            if (ids.length == 0) {
                Notify.danger('未选中任何课件');
                return ;
            }
            $.post($('#essay-content-creat-btn').data('url'),{materialIds:ids,chapterId:chapterId},function(){
                Notify.success('添加成功！');
                window.location.reload();
            }).error(function(){
                Notify.danger('添加失败！');
            });
        }); 
    }
});