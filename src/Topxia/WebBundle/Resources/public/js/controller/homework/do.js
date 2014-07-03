define(function(require, exports, module) {

    var Widget = require('widget');

    exports.run = function() {
        var questionSet = new QuestionSet({
            element: '#homework-set'
        });
    };

    var QuestionSet = Widget.extend({
        attrs: {

        },

        events: {
            'click .question-choices > li': 'onClickChoice'

        },

        setup: function() {
            console.log('question set setup.');
        },

        onClickChoice: function(event) {
            var $trigger = $(event.currentTarget);

            $('#' + $trigger.data('for')).


            console.log($trigger.data('for'));
        }





    });

});