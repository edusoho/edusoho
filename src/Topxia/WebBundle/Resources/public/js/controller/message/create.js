define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $('#course-create-form').on('click', '#course-create-btn', function(e) {
            if ($("#message_content").val().length >= 500) {
                Notify.danger("不好意思，私信内容长度不能超过500!");
                return false;
            }

            if($("#message_content").val().length == 0){
                Notify.danger("不好意思，私信内容为空!");
                return false;
            }
            
        });

    };

});