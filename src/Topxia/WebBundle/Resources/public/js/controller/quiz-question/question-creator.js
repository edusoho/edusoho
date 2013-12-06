define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    var Handlebars = require('handlebars');
    var Validator = require('bootstrap.validator');


    var QuestionCreator = Widget.extend({
        attrs: {
            targets: [],
            targetDefault: '0',
            type: 'choice',
            form : null,
            validator : null,
        },

        events: {
            'click [data-role=add-choice]': 'onAddChoice',
            'click [data-role=delete-choice]': 'onDeleteChoice',
            'click [data-role=submit]': 'setSubmission',
        },

        setup: function() {
            var $form = $('#question-create-form');
            this.set('type',$form.find('[name=type]').val());
            this.set('form',$form);
            this.set('validator', this._createValidator($form));
            this._setupFormHtml();
            
            if(this.get('type') == 'choice'){
                this._setupForTypeChoice();
            }else if(this.get('type') == 'essay'){
                this._setupForTypeEssay();
            }
        },

        onAddChoice: function(event) {
            var choiceCount = this.$('[data-role=choice]').length;
            if (choiceCount >= 26) {
                Notify.danger("选项最多二十六个!");
                return false;
            }
            this.addChoice();
        },

        onDeleteChoice: function(event) {
            var choiceCount = this.$('[data-role=choice]').length;
            if (choiceCount <= 2 ) {
                Notify.danger("选项至少要两个!");
                return false;
            }
            this.deleteChoice(event);
        },

        addChoice: function(){
            var template = this.get('choiceTemplate');
            var choiceCount = this.$('[data-role=choice]').length;
            var code = String.fromCharCode(choiceCount + 65);
            var choice = {code: code,id:choiceCount}
            var $html = $(template(choice));
            $html.appendTo(this.$('[data-role=choices]'));
            this.get("validator").addItem({
                element: '#item-input-'+choice.id,
                required: true
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

        prepareFormData: function(){
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

        setSubmission: function(e){
            var submission = $(e.currentTarget).data('submission')
            var $form = $(this.get('form'));
            $form.find('[name=submission]').val(submission);
        },

        _createValidator: function($form){
            var self = this;

            validator = new Validator({
                element: $form,
                autoSubmit: false
            });

            validator.addItem({
                element: '#question-stem-field',
                required: true
            });

            validator.on('formValidated', function(error, msg, $form) {
                if (error) {
                    return false;
                }
                if(self.get('type') =='choice' && !self.prepareFormData()){
                    return false;
                }
                self.get('validator').set('autoSubmit',true);
            });

            return validator;
        },

        _setupFormHtml: function() {
            var targets = $.parseJSON(this.$('[data-role=targets-data]').html());
            this.set('targets', targets);
        },

        _setupForTypeChoice: function() {
            var choiceTemplate = Handlebars.compile(this.$('[data-role=choice-template]').html());
            this.set('choiceTemplate', choiceTemplate);

            for (var i = 0; i < 4; i++) {
                this.addChoice();
            }
        },

        _setupForTypeEssay: function() {
            this.get("validator").addItem({
                element: '#question-answer-field',
                required: true
            });
        },


        _onChangeTargets: function(targets) {
            var options = '';
            if(typeof (targets.default)  != 'undefined'){
                var selected = targets.default;
                delete targets.default;
            }
            $.each(targets, function(index, target){
                var value = target.type+'-'+target.id;
                if(value == selected){
                    options += '<option selected=selected value=' + value + '>' + target.name + '</option>';
                }else{
                    options += '<option value=' + value + '>' + target.name + '</option>';
                }
            });
            this.$('[data-role=target]').html(options);
        },

    });

    module.exports = QuestionCreator;
});