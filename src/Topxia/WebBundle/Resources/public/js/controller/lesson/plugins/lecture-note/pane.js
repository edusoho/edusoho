define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');
    var Widget = require('widget');
    Validator = require('bootstrap.validator');

    var LectureNotePane = Widget.extend({
        attrs: {
            plugin: null
        },
        events: {},
        setup: function() {},
        show: function() {
            this.get('plugin').toolbar.showPane(this.get('plugin').code);
            var pane = this,
            toolbar = pane.get('plugin').toolbar;
            $.get(pane.get('plugin').api.init, {
                courseId: toolbar.get('courseId'),
                lessonId: toolbar.get('lessonId')
            }, function(html) {
                pane.element.html(html);
            });

        }
    });

    module.exports = LectureNotePane;

});