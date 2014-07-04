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
            list: null,
            card: null
        },

        setup: function() {
            var list = new QuestionSetList({
                element: this.$('.question-set-main'),
                questionSet: this
            });

            var card = new QuestionSetCard({
                element: this.$('.question-set-card'),
                questionSet: this
            });

            this.set('list', list);
            this.set('card', card);
        },

    });

    var QuestionSetCard = Widget.extend({
        attrs: {
            questionSet: null
        },

        setup: function() {
            var card = this;
            this.get('questionSet').on('answer_change', function(answerData) {
                if (answerData.answer.length > 0) {
                    card.activeQuestionIndex(answerData.questionId);
                } else {
                    card.deactiveQuestionIndex(answerData.questionId);
                }
            });
        },

        activeQuestionIndex: function(questionId) {
            this.element.find('.for-question-' + questionId).addClass('question-index-active');
        },

        deactiveQuestionIndex: function(questionId) {
            this.element.find('.for-question-' + questionId).removeClass('question-index-active');
        },

    });


    var QuestionSetList = Widget.extend({
        attrs: {
            questionSet: null
        },

        events: {
            'click .question-choices > li': 'onClickChoice',
            'click .question-choices-inputs input': 'onClickInputChoiceInput',
            'click .question-choices-inputs > label': 'onClickInputChoiceLabel',
            'click .question-index': 'onClickSetCard',
            'click .homework-question-essay-input-short': 'onClickEssay',
            'click .homework-essay-textarea-pack-up': 'onClickEssayPackup',
        },

        setup: function() {

        },

        onClickInputChoiceInput: function(event) {
            console.log('input click');
            event.preventDefault();
            // event.stopPropagation();
        },

        onClickInputChoiceLabel: function(event) {
            console.log('label click');
            event.preventDefault();
            // event.stopPropagation();
            // event.stopImmediatePropagation();
            console.log(event);
            console.log($(event.target).is('input'));
            // event.preventDefault();
            var $answer = $(event.currentTarget).find('input');
            console.log($answer[0]);
            this._setChoiceQuestionAnswer($answer);
        },

        onClickChoice: function(event) {
            var $answer = $('#' + $(event.currentTarget).data('for'));
            this._setChoiceQuestionAnswer($answer);
        },

        _setChoiceQuestionAnswer: function($answer)  {
            console.log('is :checked:', $answer.is(":checked"));
            console.log('prop checked', $answer.prop('checked'));
            if ($answer.is(":checked") == true){
                $answer.prop('checked', false);
            } else {
                $answer.prop('checked', true);
            }

            var $question = $answer.parents('.question');

            var answer = [];
            $question.find('.question-choices-inputs').find('input:checked').each(function(){
                answer.push($(this).val());
            });

            this.get('questionSet').trigger('answer_change', {questionId:$question.data('questionId'), answer:answer});
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