define(function(require, exports, module) {
    
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
           
        // var validator = new Validator({
        //     element: '#course-copy-form',
        //     // autoSubmit: false,
        //     triggerType: 'change',
        //     onFormValidated: function(error) {
        //         if (error) {
        //             return false;
        //             }
        //          $('#course-copy-btn').button('submiting').addClass('disabled');
        //         }
        // });

        // validator.addItem({
        //     element: '[name="title"]',
        //     required: true
        // });

        var title =$('#course_old_title').data('title');
        $('#course_old_title').attr('value', title);

    };
})