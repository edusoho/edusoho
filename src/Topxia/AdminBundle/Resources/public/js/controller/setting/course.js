define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var currentNum = $('[name="live_student_capacity"]').data('value');

        var validator = new Validator({
                element: '#course-form'
            });
        
        validator.addItem({
            element: '[name="perLiveMaxStudentNum"]',
            rule: 'integer max{max: '+ currentNum + '}'
        });

    };

});