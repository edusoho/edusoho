define(function(require, exports, module) {

    var Widget = require('widget');
    var EditorFactory = require('common/kindeditor-factory');
    var changeAnswers = {};

    var editor = EditorFactory.create(".question-essay-input-long", 'simple',{
        extraFileUploadParams:{group:'default'},
        afterChange:function(){
            this.sync();
            var essayInput = $('.question-essay-input-long').parents('.question');
            var essayQuestionId = essayInput.data('questionId');
            $answerArr = {}
            if (this.html() != "") {
                $answerArr[0] = this.html();
                changeAnswers[essayQuestionId] = {answer:$answerArr,questionId:essayQuestionId};
                $('.question-set-card').find('.for-question-' + essayQuestionId).addClass('question-index-active');
            } else {
                $('.question-set-card').find('.for-question-' + essayQuestionId).removeClass('question-index-active');
                
            }
        }
    });

    exports.run = function() {
        var questionSet = new QuestionSet({
            element: '#homework-set'
        });

        // $('.question-set-card').css('width','265px'};
        $('.question-set-card').affix({
            offset: {
              // top: 100 
            }
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
                var questionId = answerData.questionId;
                changeAnswers[questionId] = answerData;
                if (answerData.answer.length > 0) {
                    card.activeQuestionIndex(answerData.questionId);
                } else {
                    card.deactiveQuestionIndex(answerData.questionId);
                }
            });
        },

        events: {
            'click #homework-finish-btn': 'onClickFinishBtn',
            'click .question-index': 'onClickSetCard',
        },

        onClickFinishBtn: function(event) {
             if (!confirm('确认要提交作业吗？')) return false;
            var $btn = $(event.currentTarget);
                $btn.button('saving');
                $btn.attr('disabled', 'disabled');

            $.post($btn.data('url'),{data:changeAnswers},function(res){
                location.href= window.location.protocol+"//"+window.location.host+"/course/"+res.courseId+"/learn#lesson/"+res.lessonId;
            });
        },

        onClickSetCard: function(event) {
            var position = $('.question-'+$(event.currentTarget).data('anchor')).offset();
            $(document).scrollTop(position.top-10);
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
            'click .question-essay-input-short': 'onClickEssay',
            'click .essay-textarea-pack-up': 'onClickEssayPackup',
            'change .question-fill-inputs input': 'onChangeFillInput',
        },

        setup: function() {
             $(".ke-container-default").hide();
        },

      

        onChangeFillInput: function(event) {
            $answer = $(event.currentTarget);
            this._setFillQuestionAnswer($answer);
        },
  
        _setFillQuestionAnswer: function($answer) {
            var $question = $answer.parents('.question');
            var answer = [];
            $question.find('.form-control ').each(function(index,item){
                var $item = $(item);
                if ($item.val() != "") {
                answer.push($item.val());
                };
            });
            this.get('questionSet').trigger('answer_change', {questionId:$question.data('questionId'), answer:answer});
        },

        onClickInputChoiceInput: function(event) {
            event.stopPropagation();
        },

        onClickInputChoiceLabel: function(event) {
            var $answer = $(event.currentTarget).find('input');
            $(event.currentTarget).find('input').prop("checked") ? $(event.currentTarget).addClass('active') : $(event.currentTarget).removeClass('active');
            this._setChoiceQuestionAnswer($answer);
        },

        onClickChoice: function(event) {
            var $answer = $('#' + $(event.currentTarget).data('for'));
            this._setChoiceQuestionAnswer($answer);
        },

        _setChoiceQuestionAnswer: function($answer)  {
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

            
        onClickEssay: function(event){

            $(".essay-textarea-pack-up").show();
            // editor.sync();
            $(".question-essay-input-short").hide();
            $(".ke-container-default").show();
            // console.log(editor.html())

        },

        onClickEssayPackup: function(event){
            
            $(this).hide();
            $(".ke-container-default").hide();
            $(".question-essay-input-short").show();
            $(".essay-textarea-pack-up").hide();
            
        }

    });

});