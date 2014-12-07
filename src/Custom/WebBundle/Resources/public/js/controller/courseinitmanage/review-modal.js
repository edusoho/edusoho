define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.raty');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var $modal = $('#review-form').parents('.modal');
        $('#my-course-rate').raty({
            path: $('#my-course-rate').data('imgPath'),
            hints: ['很差', '较差', '还行', '推荐', '力荐'],
            score: function() {
                return $(this).attr('data-rating');
            },
            click: function(score, event) {
                $('#review_rating').val($("#my-course-rate > input[name=score]").val());

            }
        });

        var validator = new Validator({
            element: '#review-form',
            autoSubmit: false
        });

        validator.addItem({
            element: '[name="nickname"]',
            required: true,
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:14} remote'
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }

            $('#create-btn').button('submiting').addClass('disabled');

            $.post($form.attr('action'), $form.serialize(), function(result) {
                if(result.status == 'ok') {
                    $modal.modal('hide');
                    Notify.success(result.message);
                    window.location.reload();
                } else {
                    $('#create-btn').button('reset').removeClass('disabled');
                    Notify.danger(result.message);
                }
            }).error(function(){
                Notify.danger('添加评价失败');
            });
        });


    };

});