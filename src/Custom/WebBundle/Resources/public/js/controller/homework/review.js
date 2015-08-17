define(function(require, exports, module) {

    var Widget = require('widget');
    var saveModule = require('../../../../homework/js/controller/homework/save.js');
    var InitIndexActiveModule = require('../../../../homework/js/controller/homework/active.js');
    require('ckeditor');
    var Notify = require('common/bootstrap-notify');
    var changeAnswers = {};
    var changeTeacherSay = {};
    exports.run = function() {
        var dashboard = new ReviewDashboard({
            element: '#homework-set'
        }).render();
    };

    var ReviewDashboard = Widget.extend({
        setup: function() {

        },

        events: {
            'click #submit-review-btn': 'onSubmit',
            'click .question-index': 'onClickSetCard'
        },

        onSubmit: function(event) {
            if (!confirm('确认要提交作业批改吗？')) return false;
            var $btn = $(event.currentTarget);
            saveModule.save($btn,'list');
        },

       onClickSetCard: function(event) {
            var position = $('.question-'+$(event.currentTarget).data('anchor')).offset();
            $(document).scrollTop(position.top-10);
        }
    });
});