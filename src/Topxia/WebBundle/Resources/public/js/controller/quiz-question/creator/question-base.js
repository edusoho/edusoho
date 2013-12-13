define(function(require, exports, module) {

    var Widget = require('widget');
    var Handlebars = require('handlebars');
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    var QuestionCreator = Widget.extend({
        attrs: {
            validator : null,
            form : null,
            targets: [],
        },    

        events: {
            'click [data-role=submit]': 'onSubmit',
        },

        setup: function() {
            this._initTarget();
            this._initForm();
        },

        onSubmit: function(e){
            var submitType = $(e.currentTarget).data('submission')
            $(this.get('form')).find('[name=submission]').val(submitType);
        },

        _initForm: function(){
            var $form = $(this.get('form'));
            this.set('form', $form);
            this.set('validator', this._createValidator($form));
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
                self.get('validator').set('autoSubmit',true);
            });

            return validator;
        },

        _initTarget: function(){
            var taget = $('[data-role=targets-data]').html();
            if(typeof taget != 'undefined'){
                this.set('targets', $.parseJSON(taget));
            }
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