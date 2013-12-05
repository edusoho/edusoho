define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    var Handlebars = require('handlebars');
    var Validator = require('bootstrap.validator');


    var QuestionCreator = Widget.extend({
        attrs: {
            targets: [],
            type: 'choice',
            form : null,
            validator : null
        },

        events: {
            'click [data-role=add-choice]' : 'onAddChoice',
            'click [data-role=delete-choice]' : 'onDeleteChoice',
        },

        setup: function() {
            var $form = $('#question-create-form');

            this.set('form', $form);

            this.set('validator', this._createValidator($form));

            this._setupFormHtml();
            if(this.get('type') == 'choice'){
                this._setupChoiceItem();
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

        addChoice: function(){
            var template = this.get('choiceTemplate');
            var choiceCount = this.$('[data-role=choice]').length;
            var code = String.fromCharCode(choiceCount + 65);
            var choice = {code: code}
            var html = template({code:code});
            this.$('[data-role=choices]').append(html);
        },

        onDeleteChoice: function(event) {
            var choiceCount = this.$('[data-role=choice]').length;
            if (choiceCount <= 2 ) {
                Notify.danger("选项至少要两个!");
                return false;
            }
            this.deleteChoice(event);
        },

        deleteChoice: function(event){
            $(event.currentTarget).parents('[data-role=choice]').remove();
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

        _createValidator: function($form){
            var that = this;

            validator = new Validator({
                element: $form,
                autoSubmit: false
            });

            validator.addItem({
                element: '#question-stem-field',
                required: true
            });

            validator.on('formValidated', function(error, msg, $form) {
                if (error || !that.prepareFormData()) {
                    return false;
                }
                $form.submit();
            });
            return validator;
        },

        _setupFormHtml: function() {
            var targets = $.parseJSON(this.$('[data-role=targets-data]').html());
            this.set('targets', targets);
            var choiceTemplate = Handlebars.compile(this.$('[data-role=choice-template]').html());
            this.set('choiceTemplate', choiceTemplate);
        },

        _setupChoiceItem: function() {
            for (var i = 0; i < 4; i++) {
                this.addChoice();
            }
        },

        _onChangeTargets: function(targets) {
            var options = '';
            $.each(targets, function(index, target){
                options += '<option value=' + target.type + '-' + target.id + '>' + target.name + '</option>';
            });

            this.$('[name=target]').html(options);
        },

    });

    module.exports = QuestionCreator;
});