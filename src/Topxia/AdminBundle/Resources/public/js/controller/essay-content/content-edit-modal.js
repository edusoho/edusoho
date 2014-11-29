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

        $('#article-material-search').on('click',function(){
            $.get($('#article-material-search').data('url'), $('#message-search-form').serialize(), function(html) {
                $('#modal').html(html);
            });
            return false;
        });

        $('#content-edit-item-list').on('click','.content-edit',function(){
            var self = $(this);
            if (!confirm('您真的要替换该素材吗？')) {
                return ;
            }
            $.post(self.data('url'),{ materialId:self.data('articleMaterialId') },function(){
                Notify.success('替换成功！');
                window.location.reload();
            }).error(function(){
                Notify.danger('替换失败！');
            });
        });
    }
});