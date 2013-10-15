define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');

    var QuizWiget = Widget.extend({

        attrs: {
            'modal': null,
            'itemCount': 0,
            'currentItemIndex': -1,
            'currentItemForm': null
        },

        events: {
            'click .choice': 'onClickChoice',
            'click input[data-role=answer]': 'onClickAnswerInput',
            'click .check-answer': 'onCheckAnswer',
            'click .next-item': 'onNextItem',
            'click .view-result': 'onViewResult',
            'click .redo-quiz': 'onRedoQuiz'
        },

        setup: function() {
            this.set('modal', this.element.parents('.modal'));
            this.set('itemCount', this.element.find('.quiz-form').length);
            this.getNextItemForm().show();
            this.setModalButtonStatus('check-answer');

            $('#modal').on('hide.bs.modal', function(e) {
                if($("#quiz").find(".quiz-forms").is(":visible")){

                    if (!confirm("真的要退出本次测验吗？")) {
                        return false;
                    } else {
                        $("#modal").off('hide.bs.modal');
                    }

                    $("#modal").off('hide.bs.modal');
                }
            });
        },

        onRedoQuiz: function(e){
            $("#modal").off('hide.bs.modal');
        },

        onClickChoice: function(e) {
            var $choice =  $(e.currentTarget),
                $form = this.get('currentItemForm');

            if ($choice.find('input[data-role=answer]').length == 0) {
                return;
            }

            if ($form.data('itemType') == 'multiple') {
                $choice.toggleClass('choice-active');
            } else {
                $choice.parents('.quiz-choices').find('.choice').removeClass('choice-active');
                $choice.addClass('choice-active');
            }

            $choice.find('input[data-role=answer]').click();
        },

        onClickAnswerInput: function(e) {
            e.stopPropagation();
        },

        onCheckAnswer: function(e) {
            var $form = this.get('currentItemForm'),
                $answers = $form.find('input[data-role=answer]'),
                self = this;
            if ($answers.filter(':checked').length == 0) {
                Notify.danger('请选择答案后提交');
                return ;
            }
            $.post($form.attr('action'), $form.serialize(), function(result) {
                $.each($answers, function() {
                    var $answer = $(this),
                        $choice = $answer.parents('.choice');

                    if (result.correctAnswers.length > 1) {
                        if ($.inArray($answer.val(), result.correctAnswers) > -1) {
                            if ($answer.is(':checked')) {
                                $choice.addClass('choice-corrected');
                                $choice.find('.choice-answer-status').text('正确');
                            } else {
                                $choice.addClass('choice-missing');
                                $choice.find('.choice-answer-status').text('漏选');
                            }
                        } else {
                            if ($answer.is(':checked')) {
                                $choice.addClass('choice-error');
                                $choice.find('.choice-answer-status').text('错误');
                            }
                        }
                    } else {
                        if ($.inArray($answer.val(), result.correctAnswers) > -1) {
                            $choice.addClass('choice-corrected');
                            $choice.find('.choice-answer-status').text('正确');
                        } else {
                            if ($answer.is(':checked')) {
                                $choice.addClass('choice-error');
                                $choice.find('.choice-answer-status').text('错误');
                            }
                        }
                    }

                    $answer.remove();
                });

                if (self.get('currentItemIndex') == (self.get('itemCount')-1)) {
                    self.setModalButtonStatus('view-result');
                } else {
                    self.setModalButtonStatus('next-item');
                }

            },'json');

        },

        onNextItem: function(e) {
            var $form = this.getNextItemForm().show();
            this.setModalButtonStatus('check-answer');
        },

        onViewResult: function(e) {
            var $btn = $(e.currentTarget),
                self = this;

            $.post($btn.data('url'), function(response) {
                self.$('.quiz-forms').hide();
                self.$('.quiz-result').append(response);
                self.setModalButtonStatus('in-result');
            });

        },

        getNextItemForm: function() {
            var index = this.get('currentItemIndex') + 1;
            if (index > this.get('itemCount')) {
                return NaN;
            }

            var $currentForm = this.$('.quiz-form').hide().eq(index);
            this.set('currentItemForm', $currentForm);
            this.set('currentItemIndex', index);

            return $currentForm;
        },

        setModalButtonStatus: function(status) {
            var $btns = this.$('.quiz-actions').find('.btn');
            $btns.hide();
            $btns.filter('.' + status).show();
        }

    });


    exports.run = function() {

        new QuizWiget({
            element: '#quiz'
        });

    };


});