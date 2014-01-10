define(function(require, exports, module) {

    var BaseQuestion = require('./question-base');
    var Handlebars = require('handlebars');
    var Uploader = require('upload');
    var Notify = require('common/bootstrap-notify');

    var ChoiceQuestion = BaseQuestion.extend({
        attrs: {
            index : 1,
        },

        events: {
            'click [data-role=add-choice]': 'onAddChoice',
            'click [data-role=delete-choice]': 'onDeleteChoice',
        },
        
        setup: function() {
            ChoiceQuestion.superclass.setup.call(this);
            this._initValidator();
            this._setupForChoice();
        },
            
        onAddChoice: function(event) {
            var choiceCount = this.$('[data-role=choice]').length;
            if (choiceCount >= 10) {
                Notify.danger("选项最多十个!");
                return false;
            }
            var choiceCount = this.$('[data-role=choice]').length;
            var code = String.fromCharCode(choiceCount + 65);
            var model = {code: code,id:this.get('index')}
            this.addChoice(model);
        },

        onDeleteChoice: function(event) {
            var choiceCount = this.$('[data-role=choice]').length;
            if (choiceCount <= 2 ) {
                Notify.danger("选项至少二个!");
                return false;
            }
            this.deleteChoice(event);
        },

        addChoice: function(model){
            var template = this.get('choiceTemplate');
            var $html = $($.trim(template(model)));

            $html.appendTo(this.$('[data-role=choices]'));
            this.get("validator").addItem({
                element: '#item-input-'+model.id,
                required: true
            });

            this.set('index', this.get('index')+1);

            var $trigger = $('#item-upload-' + model.id);
            var uploader = new Uploader({
                trigger: $trigger,
                name: 'file',
                action: this.element.data('uploadUrl'),
                accept: 'image/*',
                error: function(file) {
                    Notify.danger('上传失败，请重试！')
                },
                success: function(response) {
                    var result = '[image]' + response.hashId + '[/image]'
                    $($trigger.data('target')).val(result);
                }
            });



        },

        deleteChoice: function(e){
            var $btn = $(e.currentTarget);
            var id = '#' + $btn.parents('.input-group').find('input:first').attr('id');
            this.get('validator').removeItem(id);
            $btn.parents('[data-role=choice]').remove();
            this.$('[data-role=choice]').each(function(index, item){
                $(this).find('.choice-label').html('选项' + String.fromCharCode(index + 65));
            });
        },


        _prepareFormData: function(){
            var answers = [],
            $form = this.get('form');
            $form.find(".answer-checkbox").each(function(index){
                if($(this).prop('checked')) {
                    answers.push(index);
                }
            });
            if (0 == answers.length){
                Notify.danger("请选择正确答案!");
                return false;
            }
            $form.find('[name=answers]').val(answers.join('|'));
            return true;
        },

        _initValidator: function(){
            var self = this;
            this.get('validator').off('formValidated');
            this.get('validator').on('formValidated', function(error, msg, $form) {
                if (error) {
                    return false;
                }
                if(!self._prepareFormData()){
                    return false;
                }

                self.get('validator').set('autoSubmit',true);
            });
        },

        _setupForChoice: function() {
            var choiceTemplate = Handlebars.compile(this.$('[data-role=choice-template]').html());
            this.set('choiceTemplate', choiceTemplate);
            
            if(typeof this.$('[data-role=choice-data]').html() != 'undefined'){
                var self = this;
                var choice = $.parseJSON(this.$('[data-role=choice-data]').html());
                var isNaswer = choice.isAnswer;
                delete choice['isAnswer'];
                $.each(choice, function() {
                    var choiceCount = self.$('[data-role=choice]').length;
                    var code = String.fromCharCode(choiceCount + 65);
                    var choiceModel = {code:code, id:this.id, content:this.content, isAnswer: (","+isNaswer+",").indexOf(this.id)>=0};
                    self.addChoice(choiceModel);
                });
            }else{
                for (var i = 0; i < 4; i++) {
                    var choiceCount = this.$('[data-role=choice]').length;
                    var code = String.fromCharCode(choiceCount + 65);
                    var model = {code: code,id:this.get('index')}
                    this.addChoice(model);
                }
            }
        },


    });

    module.exports = ChoiceQuestion;

});


