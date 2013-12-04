define(function(require, exports, module) {

    var Widget = require('widget');
    var Handlebars = require('handlebars');

    var QuestionCreator = Widget.extend({
        attrs: {
            targets: []
        },

        events: {
            'click [data-role=add-choice]' : 'onAddChoice',
            'click [data-role=delete-choice]' : 'onDeleteChoice',
        },

        setup: function() {
            var targets = $.parseJSON(this.$('[data-role=targets-data]').html());
            this.set('targets', targets);

            var choiceTemplate = Handlebars.compile(this.$('[data-role=choice-template]').html());
            this.set('choiceTemplate', choiceTemplate);
        },

        onAddChoice: function(event) {
            var template = this.get('choiceTemplate');

            var choiceCount = this.$('[data-role=choice]').length;
            console.log(choiceCount);
            var code = String.fromCharCode(choiceCount + 65);

            var choice = {code: code}

            var html = template({code:code});
            this.$('[data-role=choices]').append(html);
        },

        onDeleteChoice: function(event) {
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