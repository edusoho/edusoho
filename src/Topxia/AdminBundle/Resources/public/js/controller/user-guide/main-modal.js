define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var modal=$("#modal");
        //点击查看时显示modal框
        modal.on('click',".view-step",function(){
            $.get($(this).data('url')+'?index='+$(this).data('index'),function(html){
                if(html){
                    modal.modal("hide");
                    $('#step-modal').html(html).modal('show');
                }else{
                    Notify.danger('步骤参数错误！');
                }   
            });
        });
    };

});