define(function (require, exports, module) {
    
    
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var messenger = require('./messeger.js');



    exports.run = function () {
        //Get Exam List
        $.get($('.js-pane-question').data('url'), function (response) {
            $('.js-pane-question').html(response);
        })

        //Get Text Track Templete
        $.get($('.panel-texttrack').data('url'))
            .done(function(data){
                $('.panel-texttrack').html(data);
            })
    }
});
