define(function(require, exports, module) {

    var Widget = require('widget');
    var Handlebars = require('handlebars');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    var QuestionCreator = Widget.extend({
        attrs: {
            validator : null,
            form : null,
            targets: [],
            category: [],
        },    

        events: {
            'click [data-role=submit]': 'onSubmit',
        },

        setup: function() {
            this._initTarget();
            this._initCategory();
            this._initForm();
        },

        onSubmit: function(e){
            var submitType = $(e.currentTarget).data('submission')
            var $form = this.get('form');
            $form.find('[name=submission]').val(submitType);
            

            // if($('[data-role=advanced-collapse]').hasClass('collapsed')){
            //     $form.find('[name=analysis]').val('');
            //     $form.find('[name=score]').val('');
            //     $form.find('[name=categoryId]').get(0).selectedIndex=0; 
            // }
        },

        _initForm: function(){
            var $form = $(this.get('form'));
            this.set('form', $form);
            this.set('validator', this._createValidator($form));
        },

        _createValidator: function($form){
            var self = this;

            Validator.addRule('fillCheck',/(\[\[(.*?)\]\])/i, '请输入正确的答案,如今天是[[晴|阴|雨]]天.');
            Validator.addRule('score',/^(\d){1,10}$/i, '请输入正确的分值');

            validator = new Validator({
                element: $form,
                autoSubmit: false
            });

            validator.addItem({
                element: '#question-stem-field',
                required: true
            });

            validator.addItem({
                    element: '#question-score-field',
                    required: false,
                    rule:'score',
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

        _initCategory: function(){
            var category = $('[data-role=category-data]').html();
            if(typeof category != 'undefined'){
                this.set('category', $.parseJSON(category));
            }
        },

        _onChangeCategory: function(targets) {
            var options = "<option value=''> 请选择类别 </option>";
            if(typeof (targets.default)  != 'undefined'){
                var selected = targets.default;
                delete targets.default;
            }
            $.each(targets, function(index, category){
                if(category.id == selected){
                    options += '<option selected=selected value=' + category.id + '>' + category.name + '</option>';
                }else{
                    options += '<option value=' + category.id + '>' + category.name + '</option>';
                }
            });
            this.$('[data-role=category]').html(options);
        },

        _onChangeTargets: function(categorys) {
            var options = '';
            if(typeof (categorys.default)  != 'undefined'){
                var selected = categorys.default;
                delete categorys.default;
            }
            $.each(categorys, function(index, target){
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