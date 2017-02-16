define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('jquery.sortable');
    
    exports.run = function() {

        $(".course-list-group").on('click','.close',function(){
            if(confirm('是否要从班级移除该课程？')){
                $.post($(this).data('url'), function(resp){
                    if(resp.success){
                        Notify.success(Translator.trans('课程移除成功!'));
                        window.location.reload();
                    }else{
                        Notify.danger(Translator.trans('操作失败:') + resp.message);
                    }
                });
            }
        });

        var $list = $(".course-list-group").sortable({
            distance: 20,
            onDrop: function (item, container, _super) {
                _super(item, container);
                //实时保存数据
                $('#courses-form').submit();
            }
        });

    };

});