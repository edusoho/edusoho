define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.raty');

    exports.run = function() {

        Validator.addRule('star', /^[1-5]$/, '请打分');

        var $form = $('#review-form');

        if ($form.length > 0) {
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
                element: $form.find('[name=rating]'),
                required: true,
                rule: 'star',
                errormessageRequired: '请打分'
            });

            validator.addItem({
                element: $form.find('[name=content]'),
                required: true
            });


            validator.on('formValidated', function(error, msg, $form) {
                if (error) {
                    return;
                }

                $form.find('.js-btn-save').button('loading');

                $.post($form.attr('action'), $form.serialize(), function(json) {
                    $form.find('.text-success').fadeIn('fast', function(){
                        window.location.reload();
                    });
                }, 'json');

            });

            $form.find('.js-btn-save').on("click", function(){
                $form.submit();
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

        }
        
        var $reviews = $('.js-reviews');
        var $fullLength = $reviews.find('.full-content').text().length;
        if( $fullLength<100){
            $reviews.find('.actions').remove();
        }

        $reviews.on('click', '.show-full-btn', function(){
            var $review = $(this).parents('.media');
            $review.find('.short-content').slideUp('fast', function(){
                $review.find('.full-content').slideDown('fast');
            });
            $(this).hide();
            $review.find('.show-short-btn').show();
        });

        $reviews.on('click', '.show-short-btn', function(){
            var $review = $(this).parents('.media');
            $review.find('.full-content').slideUp('fast', function(){
                $review.find('.short-content').slideDown('fast');
            });
            $(this).hide();
            $review.find('.show-full-btn').show();
        });
  

      

    };

});