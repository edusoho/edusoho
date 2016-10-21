define(function (require, exports, module) {

    var Widget = require('widget');

    var TaskEditor = Widget.extend({

        attrs: {
            element: '#task-editor',
            type: '',
            step: 1,
            form: null
        },

        events: {
            'click #course-tasks-next': 'onNext',
            'click #course-tasks-prev': 'onPrev'
        },

        onNext: function (event) {
            var step = this.get('step');
            if (step >= 3) {
                return;
            }

            this.set('step', step + 1);
            this._switchPage();
        },

        onPrev: function (event) {
            var step = this.get('step');

            if (step <= 1) {
                return;
            }

            this.set('step', step - 1);
            this._switchPage();
        },

        _switchPage: function () {
            var step = this.get('step');
            // show 各个step的页面 隐藏其他step的页面;
            step == 2 && this.set('form', "#step2-from")
        }
    });

    exports.export = TaskEditor;
});