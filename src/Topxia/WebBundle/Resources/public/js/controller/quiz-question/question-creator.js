define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    var Handlebars = require('handlebars');

    var QuestionCreator = Widget.extend({
        attrs: {
            targets: [],
            type: 'choice'
        },

        events: {
            'click [data-role=add-choice]' : 'onAddChoice',
            'click [data-role=delete-choice]' : 'onDeleteChoice',
        },

        setup: function() {
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