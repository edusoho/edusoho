define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $('#save-btn').on('click',function(){
            var self = $(this);
            var answer = [];
            $('[data-type]:checked').each(function(index,item) {
                answer.push($(item).val());
            });

            $.post(self.data('url'),{answer:answer},function(response){
                if(response.answer){
                    $('#answer-show').removeClass('hide red').addClass('green');
                    self.addClass('disabled');
                } else {
                    $('#answer-show').removeClass('hide');
                }
            });
        });

        $('.modal').on('hidden.bs.modal', function (e) {
            window.mediaExercise.player.resume();
        });
    };

});