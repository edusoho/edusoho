define(function (require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var CourseSetClone = require('./clone');

    exports.run = function (options) {

        var csl = new CourseSetClone();

        var validator = new Validator({
            element: '#course-copy-form',
            failSilently: true,
            autoSubmit: false,
            onFormValidated: function(error, results, $form){
                if (error) {
                    return false;
                }
                let $webUrl = $('#js-course-clone-btn').data('webUrl');
                let $crontabUrl = $('#js-course-clone-btn').data('crontabUrl');
                csl.doClone($("#js-course-clone-btn").data('courseSetId'),$('#course_title').val(), $webUrl, $crontabUrl);

            }
        });

        validator.addItem({
            element: '#course-copy-form [name="title"]',
            required: true,
        });


    };

});
