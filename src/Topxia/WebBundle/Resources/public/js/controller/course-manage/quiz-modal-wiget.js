define(function(require, exports, module) {

    require('ckeditor');
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    var Validator = require('bootstrap.validator');
    var Handlebars = require('handlebars');

    var QuizModalWiget = Widget.extend({

        attrs: {
            form : '#quiz-form',
            validator : null
        },

        choiceGlobalId: 0,
        
        events: {
            'click [data-role=quiz-item-add]': 'onAddItem',
            'click [data-role=quiz-item-edit]': 'onEditItem',
            'click [data-role=quiz-item-delete]': 'onDeleteItem',
            'click [data-role=option-add]': 'onAddChoice',
            'click [data-role=option-delete]': 'onDeleteChoice'
        },

        setup: function() {
            var $form = $('#quiz-form');

            this.set('form', $form);
            this.set('validator', this._createValidator($form));

            this.$('[data-role=quiz-item-add]').click();

            this.renderItems();
        },

        renderItems: function() {
            var model = $.parseJSON(this.$('[data-role=items-model]').text());
            var template = Handlebars.compile(this.$('[data-role=item-template]').html());

            var html = [], items = {};
            $.each(model, function() {
                items[this.id] = this;
                html.push(template(this));
            });

            this.set('items', items);
            this.set('itemTemplate', template);
            this.$('[data-role=items]').html(html.join(''));
        },

        onEditItem: function(e) {
            var $item = $(e.currentTarget);
            var self = this;

            var itemModel = this.get('items')[$item.data('id')];

            this.clearChoices();

            for (var i=0; i < itemModel.choices.length; i++)
            {
                var choiceModel = {index:i, name:itemModel.choices[i], isAnswer: ($.inArray(i+'', itemModel.answers)>-1)};
                self.addChoice(choiceModel);
            }

            var $form = this.get('form');
            $form.find('[name=description]').val(itemModel.description);
            $form.find('[name=level][value="' + itemModel.level +'"]').click();
            $form.find('[name=id]').val(itemModel.id);
            $form.find('[name=answers]').val('');

            this.$('[data-role=items]').find('.list-group-item').removeClass('active');
            $item.addClass('active');
            $form.find('[name=description]').focus();
        },

        onDeleteItem: function(e){
            e.stopPropagation();
            var $btn = $(e.currentTarget),
                $item = $btn.parents('.list-group-item'),
                $items = this.$('[data-role=items]'),
                self = this;

            if (!confirm('真的要删除该题目吗？')) {
                return ;
            }

            var url = $items.data('deleteUrl').replace(/__id__/, $item.data('id'));
            $.post(url, function(){
                $item.remove();
                self.get('items')[$item.data('id')] = undefined;
                Notify.success('测验题目删除成功！');
            });
        },

        onAddItem: function(e){
            this.resetItemForm();
        },

        clearChoices: function() {
            var self = this,
                $form = this.get('form');
            $form.find('.item-input').each(function(){
                self.get('validator').removeItem('#' + $(this).attr('id'));
            });

            $form.find('.options').html('');
        },

        addChoice: function(model){
            model.code = String.fromCharCode(model.index + 65);
            model.globalId = this.getNextChoiceGlobalId();
            var template = Handlebars.compile(this.$('[data-role=choice-template]').html());

            var $html = $($.trim(template(model)));

            $html.appendTo(this.get('form').find('.options'));

            var id = $html.find('.item-input').attr('id');

            this.get("validator").addItem({
                element: '#'+id,
                required: true
            });

            return $html;
        },

        onAddChoice: function(e){
            var length = this.get('form').find('.options').children().length;
            var model = {index:length};
            var $choice = this.addChoice(model);
            $choice.find('.item-input').focus();
        },

        onDeleteChoice: function(e){
            var $btn = $(e.currentTarget),
                $controlGroup = $btn.parents('.control-group'),
                $choices = this.get('form').find('.options'),
                self = this;

            if($choices.children().length == 2){
                Notify.danger("每道题目的选项不得少于两个!");
                return false;
            }

            self.get("validator").removeItem('#' + $controlGroup.find('.item-input').attr('id'));

            $controlGroup.remove();

            $choices.find('.choice-label').each(function(index) {
                $(this).text('选项' + String.fromCharCode(index + 65));
            });

        },

        prepareFormData: function(e) {
            var answers = [],
                $form = this.get('form');

            $form.find(".answer-checkbox").each(function(index){
                if($(this).prop('checked')) {
                    answers.push(index);
                }
            });

            if (0 == answers.length){
                Notify.danger("您尚未选择正确答案,请选择正确答案,或事先增加新的选项!");
                return false;
            }

            $form.find('[name=answers]').val(answers.join(';'));

            return true;
        },

        _createValidator: function($form){
            var self = this;

            validator = new Validator({
                element: $form,
                autoSubmit: false
            });

            validator.addItem({
                element: '#quiz-description-field',
                required: true
            });

            validator.on('formValidated', function(error, msg, $form) {

                if (error || !self.prepareFormData()) {
                    return false;
                }

                $.post($form.attr('action'), $form.serialize(), function(item) {
                    var items = self.get('items'),
                        $items = self.$('[data-role=items]');
                    var itemHtml = self.get('itemTemplate')(item);
                    if (items[item.id]) {
                        $items.find('[data-id=' + item.id + ']').replaceWith(itemHtml);
                        $items.find('[data-id=' + item.id + ']').addClass('active');
                        Notify.success('测验题目保存成功！');
                    } else {
                        $items.append(itemHtml);
                        self.resetItemForm();
                        Notify.success('测验题目添加成功，您可继续添加！');
                    }
                    items[item.id] = item;
                }, 'json');
            });
            return validator;
        },

        resetItemForm: function() {
            var $form = this.get('form');

            $form.find('[name=id]').val('');
            $form.find('[name=description]').val('');
            $form.find('[name=level]:eq(1)').click();
            $form.find('[name=answers]').val('');

            this.clearChoices();
            for (var i = 0; i < 4; i++) {
                this.addChoice({index:i});
            };

            this.$('[data-role=items]').find('.list-group-item').removeClass('active');

            $form.find('[name=description]').focus();
        },

        getNextChoiceGlobalId: function() {
            return this.choiceGlobalId ++;
        }

    });
    
    module.exports = QuizModalWiget;
});