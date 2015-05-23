define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.raty');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var $form = $('#review-form');

        $form.find('.rating-btn').raty({
            path: $form.find('.rating-btn').data('imgPath'),
            hints: ['很差', '较差', '还行', '推荐', '力荐'],
            score: function() {
                return $(this).attr('data-rating');
            },
            click: function(score, event) {
                $form.find('[name=rating]').val(score);
            }
        });

        var validator = new Validator({
            element: $form,
            autoSubmit: false
        });

        validator.addItem({
            element: '[name=rating]',
            required: true,
            errormessageRequired: '请打分'
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }

            $form.find('[type=submit]').button('loading');

            $.post($form.attr('action'), $form.serialize(), function(json) {
                $form.find('.text-success').fadeIn('fast', function(){
                    window.location.reload();
                });
            }, 'json');

        });


        $('.js-hide-review-form').on('click', function(){
            $(this).hide();
            $('.js-show-review-form').show();
            $form.hide();
        });

        $('.js-show-review-form').on('click', function(){
            $(this).hide();
            $('.js-hide-review-form').show();
            $form.show();
        });

    };

});