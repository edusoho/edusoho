define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.raty');
    require('common/validator-rules').inject(Validator);
    var UserSign = require('../../../../topxiaweb/js/util/sign.js');

    exports.run = function() {

        var buy_btn = false;
        
        $('.buy-btn').click(function() {
            if (!buy_btn) {
                $('.buy-btn').addClass('disabled');
                buy_btn = true;
            }
            return true;
        });

        $(".cancel-refund").on('click', function(){
            if (!confirm('真的要取消退款吗？')) {
                return false;
            }

            $.post($(this).data('url'), function(){
                window.location.reload();
            });
        });
        
        $("#quit").on('click', function(){
            if (!confirm('确定退出班级吗？')) {
                return false;
            }

            $.post($(this).data('url'), function(){
                window.location.reload();
            });
        });

        if ($('div .reviewDiv').length > 0 ) 
        {
            $('#review').on('click', '.review', function() {
                    $(this).hide();
                    $('#review-form').show();
                    $('.unreview').css('display', "");
                });

                $('#review').on('click', '.unreview', function() {
                    $(this).hide();
                    $('#review-form').hide();
                    $('.review').css('display', "");

                });

                $('#review-form').find('#my-course-rate').raty({
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
                    element: '[name="review[rating]"]',
                    required: true,
                    errormessageRequired: '请打分'
                });

                validator.on('formValidated', function(error, msg, $form) {
                    if (error) {
                        return;
                    }

                    $.post($form.attr('action'), $form.serialize(), function(json) {
                        window.location.reload();
                    }, 'json');

                });
        }

        if ($('#classroom-sign').length > 0) {
            var userSign = new UserSign({
            element: '#classroom-sign',
            });
        }

    };

});