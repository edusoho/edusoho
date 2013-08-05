define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.raty');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        $('#my-course-rate').raty({
            path: '/assets/img/raty',
            hints: ['很差', '较差', '还行', '推荐', '力荐'],
            score: function() {
                return $(this).attr('data-rating');
            },
            click: function(score, event) {
                $('#review_rating').val($("#my-course-rate > input[name='score']").attr("value"));

            }
        });

        var validator = new Validator({
            element: '#review-form',
            autoSubmit: false
        });

        validator.addItem({
            element: '[name="review[rating]"]',
            required: true,
            errormessageRequired: '请打分'
        });

        validator.addItem({
            element: '[name="review[title]"]',
            required: true
        });

        validator.addItem({
            element: '[name="review[content]"]',
            required: true
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }

            $.post($form.attr('action'), $form.serialize(), function(json) {
                window.location.reload();
            }, 'json');

        });


    };

});