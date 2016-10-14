define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.raty');
    var Notify = require('common/bootstrap-notify');
    var ThreadShowWidget = require('../thread/thread-show.js');

    exports.run = function() {

        Validator.addRule('star', /^[1-5]$/, Translator.trans('请打分'));

        var $form = $('#review-form');

        if ($form.length > 0) {
            $form.find('.rating-btn').raty({
                path: $form.find('.rating-btn').data('imgPath'),
                hints: [Translator.trans('很差'), Translator.trans('较差'), Translator.trans('还行'), Translator.trans('推荐'), Translator.trans('力荐')],
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
                errormessageRequired: Translator.trans('请打分')
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

        $('.js-reviews').hover(function(){
            var $fullLength = $(this).find('.full-content').text().length;
            
            if( $fullLength > 100 && $(this).find('.short-content').is(":hidden") == false){
                $(this).find('.show-full-btn').show();
            } else {
                $(this).find('.show-full-btn').hide();
            }
        })

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
  
        var threadShowWidget = new ThreadShowWidget({
            element: '.js-reviews',
        });
      
        threadShowWidget.undelegateEvents('.js-toggle-subpost-form', 'click');
        $('.js-toggle-subpost-form').click(function(e){
            e.stopPropagation();
            
            var postNum = $(this).closest('.thread-subpost-container').find('.thread-subpost-content .thread-subpost-list .thread-subpost').length;
            
            if (postNum >= 5) {
                Notify.danger('评论回复已达5条上限，不能再回复!');
                return;
            }
            var $form = $(this).parents('.thread-subpost-container').find('.thread-subpost-form');
            $form.toggleClass('hide');
            threadShowWidget._initSubpostForm($form);
        })
    };

});