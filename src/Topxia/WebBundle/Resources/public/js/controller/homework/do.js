define(function(require, exports, module) {

    var Widget = require('widget');
    var saveModule = require('./save.js');
    var InitIndexActiveModule = require('./active.js');
    require('ckeditor');
    var Notify = require('common/bootstrap-notify');
    var BatchAttachmentsUploader = require('../../../../topxiaweb/js/controller/quiz-question/batch-upload-attachments');
    var UploadQuestionAttachments = require('../../../../topxiaweb/js/controller/quiz-question/upload-question-attachments');
    var changeAnswers = {};
    var changeTeacherSay = {};
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
                element: this.$('.question-set-side'),
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
            InitIndexActiveModule.QuestionIndexActive();

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
            'click #homework-check-btn': 'onClickCheckBtn',
            'click #homework-save-btn': 'onClickSaveBtn',
            'click .question-index': 'onClickSetCard',
            'click #homework-finish': 'onClickFinishConfirm',
            'click #homework-next-do': 'onClickNextDoConfirm'
        },

        onClickFinishBtn: function(event) {
            var $btn = $(event.currentTarget);
            saveModule.save($btn,'back');
        },

        onClickCheckBtn: function(event) {
            if (!confirm('确认要提交作业批改吗？')) return false;
            var $btn = $(event.currentTarget);
            saveModule.save($btn,'list');
        },

        onClickSaveBtn: function(event) {
            //if (!confirm('确认要下次再做吗？')) return false;
            var $btn = $(event.currentTarget);
            saveModule.save($btn,'back');
        },

        onClickSetCard: function(event) {
            var position = $('.question-'+$(event.currentTarget).data('anchor')).offset();
            $(document).scrollTop(position.top-10);
        },

        onClickFinishConfirm: function() {
            $('#homework-finished-dialog').modal('show');
        },

        onClickNextDoConfirm: function() {
            $('#homework-save-dialog').modal('show');
        },

        activeQuestionIndex: function(questionId) {
            this.element.find('.for-question-' + questionId).addClass('question-index-active');
        },

        deactiveQuestionIndex: function(questionId) {
            this.element.find('.for-question-' + questionId).removeClass('question-index-active');
        }

    });

    var QuestionSetList = Widget.extend({
        attrs: {
            questionSet: null
        },

        events: {
            'click .question-choices > li': 'onClickChoice',
            'click .question-choices-inputs input': 'onClickInputChoiceInput',
            'click .question-choices-inputs > label': 'onClickInputChoiceLabel',
            'focus .question-essay-input-short': 'onClickEssay',
            'click .essay-textarea-pack-up': 'onClickEssayPackup',
            'change .question-fill-inputs input': 'onChangeFillInput',
            'change .question-teacher-say-input': 'onChangeTeacherSayInput'
        },

        setup: function() {
             $(".ke-container-default").hide();
        },

        onChangeTeacherSayInput: function(event) {
            var teacherSay = [];
            var questionIds = [];

            $teacherCheck = $(event.currentTarget);

            $teacherCheck.parents().find('.teacher-say').each(function(index,item){
                var $item = $(item);
                teacherSay.push($item.val());
                questionIds.push($item.data('questionId'));
            });

            changeTeacherSay = {teacherSay:teacherSay,questionIds:questionIds};
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
            return false;
        },

        onClickInputChoiceLabel: function(event) {
            var $answer = $(event.currentTarget).find('input');
            if ($answer.prop("checked")) {
                $answer.prop("checked",false);
                this._setChoiceQuestionAnswer($answer);
                return false;
            };
            if (!$answer.prop("checked")) {
                $answer.prop("checked",true);
                this._setChoiceQuestionAnswer($answer);
                return false;
            };
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

        _setEssayQuestionAnswer: function($questionId,$answer) {
            this.get('questionSet').trigger('answer_change', {questionId:$questionId, answer:$answer});
        },



        _initBatchAttachmentUploader: function(editor){

                    
                $("#cloud-btn", "#question-attachment").on('click',function(){
                    var url="";
                    if($(this).data("storage")!='cloud' || typeof(FileReader)=="undefined" || typeof(XMLHttpRequest)=="undefined"){
                        url = $(this).data("normalUrl");
                    } else {
                        url = $(this).data("html5Url");
                    }
                    $("#modal").html('');
                    $("#modal").modal('show');
                    $.get(url, function(html){
                        $("#modal").html(html);
                        if($('#selectFiles').length > 0){
                            var batchAttachmentsUploader = new BatchAttachmentsUploader({
                                 editor:editor
                            });
                        }else{
                            var uploadQuestionAttachments = new UploadQuestionAttachments({
                                 editor:editor
                            });
                        }
                         
                       
                    });
                })
              
        },



        onClickEssay: function(event){
            var $shortTextarea = $(event.currentTarget);
            var $longTextarea = $shortTextarea.parent().find('.question-essay-input-long');
            var $shortTextarea = $shortTextarea.parent().find('.question-essay-input-short');
            var $textareaBtn = $shortTextarea.parent().find('.essay-textarea-pack-up');
            var $uploadBtn = $('#question-attachment');

            $shortTextarea.parent().find(".ke-container-default").show();
            $textareaBtn.show();
            $shortTextarea.hide();
            $uploadBtn.show();


            var essayQuestionId = $shortTextarea.data('questionId');

            var editor = CKEDITOR.replace($longTextarea.attr('id'), {
                toolbar: 'Minimal',
                filebrowserImageUploadUrl: $longTextarea.data('imageUploadUrl')
            });

            this._initBatchAttachmentUploader(editor);

            editor.on('blur', function(e) {
                editor.updateElement();
                setTimeout(function() {
                    $longTextarea.val(editor.getData());
                    $longTextarea.change();
                }, 1);
            });

            editor.on('instanceReady', function(e){
                this.focus();
                $textareaBtn.one('click', function() {
                    $shortTextarea.val($(editor.getData()).text());
                    editor.destroy();
                    $longTextarea.hide();
                    $textareaBtn.hide();
                    $shortTextarea.show();
                });
            });

            editor.on('key', function(){
                editor.updateElement();
                setTimeout(function() {
                    $longTextarea.val(editor.getData());
                    $longTextarea.change();
                    var answer = [];
                    if ($longTextarea.val() != '') {
                        answer.push($longTextarea.val());
                        changeAnswers[essayQuestionId] = {answer:answer,questionId:essayQuestionId};
                        $('.question-set-card').find('.for-question-' + essayQuestionId).addClass('question-index-active');
                    } else {
                        $('.question-set-card').find('.for-question-' + essayQuestionId).removeClass('question-index-active');
                    }
                    $shortTextarea.text($longTextarea.val());
                }, 1);
            }); 

            editor.on('insertHtml', function(e) {
                editor.updateElement();
                setTimeout(function() {
                    $longTextarea.val(editor.getData());
                    $longTextarea.change();
                    var answer = [];
                    if ($longTextarea.val() != '') {
                        answer.push($longTextarea.val());
                        changeAnswers[essayQuestionId] = {answer:answer,questionId:essayQuestionId};
                        $('.question-set-card').find('.for-question-' + essayQuestionId).addClass('question-index-active');
                    } else {
                        $('.question-set-card').find('.for-question-' + essayQuestionId).removeClass('question-index-active');
                    }
                    $shortTextarea.text($longTextarea.val());
                }, 1);
            });
        },

        onClickEssayPackup: function(event){
            var $this = $(event.currentTarget);
            $this.parent().find(".ke-container-default").hide();
            $this.parent().find(".question-essay-input-short").show();
            $this.parent().find(".essay-textarea-pack-up").hide();
        }
    });
});