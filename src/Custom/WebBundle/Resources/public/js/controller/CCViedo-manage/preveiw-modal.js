define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $('#save-btn').on('click',function(){
            var self = $(this);
            $.post(self.data('url'),$('#answer-form').serialize(),function(response){
                if(response.answer){
                    Notify.success(response.message);
                    $('#answer-show').removeClass('hide red').addClass('green');
                } else {
                    Notify.danger(response.message);
                    $('#answer-show').removeClass('hide');
                }
            });
        });

        $('#cancel-btn .close').on('click',function(){
            window.mediaExercise.player.resume();
        });
    };

});