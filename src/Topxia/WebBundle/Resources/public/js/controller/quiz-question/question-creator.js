define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    var Handlebars = require('handlebars');

    var QuestionCreator = Widget.extend({
        attrs: {
            targets: []
        },

        index: 4,

        events: {
            'click [data-role=add-choice]' : 'onAddChoice',
            'click [data-role=delete-choice]' : 'onDeleteChoice',
        },

        setup: function() {
            var targets = $.parseJSON(this.$('[data-role=targets-data]').html());
            this.set('targets', targets);

            var choiceTemplate = Handlebars.compile(this.$('[data-role=choice-template]').html());
            this.set('choiceTemplate', choiceTemplate);
            
            for (var i = this.get('index'); i > 0; i--) {
                this.onAddChoice();
            }
        },

        onAddChoice: function(event) {
            var template = this.get('choiceTemplate');

            var choiceCount = this.$('[data-role=choice]').length;

            if (choiceCount > 25) {
                Notify.danger("每道题目的选项不得多于二十六个!");
                return false;
            }

            var code = String.fromCharCode(choiceCount + 65);

            var choice = {code: code}

            var html = template({code:code});
            this.$('[data-role=choices]').append(html);
        },

        onDeleteChoice: function(event) {
            var choiceCount = this.$('[data-role=choice]').length;
            if (choiceCount < 3 ) {
                Notify.danger("每道题目的选项不得少于两个!");
                return false;
            }

            $(event.currentTarget).parents('[data-role=choice]').remove();

            this.$('[data-role=choice]').each(function(index, item){
                $(this).find('.choice-label').html('选项' + String.fromCharCode(index + 65));
            });

        },

        _onChangeTargets: function(targets) {
            var options = '';
            $.each(targets, function(index, target){
                options += '<option value=' + target.type + '-' + target.id + '>' + target.name + '</option>';
            });

            this.$('[name=target]').html(options);
        }

    });

    module.exports = QuestionCreator;
});