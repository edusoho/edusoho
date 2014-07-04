define(function(require, exports, module) {

    var Widget = require('widget');
    var EditorFactory = require('common/kindeditor-factory');

    exports.run = function() {
        var questionSet = new QuestionSet({
            element: '#homework-set'
        });
    };

    var QuestionSet = Widget.extend({
        attrs: {

        },

        events: {
            'click .question-choices > li': 'onClickChoice',
            'click .question-index': 'onClickSetCard',
            'click .homework-question-essay-input-short': 'onClickEssay',
            'click .homework-essay-textarea-pack-up': 'onClickEssayPackup',

        },

        setup: function() {
            console.log('question set setup.');
        },

        onClickChoice: function(event) {
            var $trigger = $(event.currentTarget);
            var $choice = $('#' + $trigger.data('for'));
            var questionId = $trigger.parents('.question').data('questionId');

            if ($choice.is(":checked") == true){
                $choice.prop('checked', false);
                $('a[data-anchor="'+questionId+'"]').css('background','');
            } else {
                $choice.prop('checked', true);
                $('a[data-anchor="'+questionId+'"]').css('background','#ebebeb');
                $('a[data-anchor="'+questionId+'"]').css('border-color','#adadad');
            }
        },

        onClickSetCard: function(event){
            var position = $('.question-'+$(event.currentTarget).data('anchor')).offset();
            $(document).scrollTop(position.top-10);
        },

        onClickEssay: function(event){

            $(".homework-essay-textarea-pack-up").show();
            var editor = EditorFactory.create(event.currentTarget, 'simple');
            editor.sync();
            $(".homework-question-essay-input-short").hide();
            $(".ke-container-default").show();

        },

        onClickEssayPackup: function(event){
            
            $(this).hide();
            $(".homework-essay-textarea-pack-up").hide();
            $(".ke-container-default").hide();
            $(".homework-question-essay-input-short").show();
        }



    });

});