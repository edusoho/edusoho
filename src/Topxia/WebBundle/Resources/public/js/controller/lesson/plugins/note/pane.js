define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');
    var Widget = require('widget');
    Validator = require('bootstrap.validator');

    var NotePane = Widget.extend({
        attrs: {
            editor: null,
            content: '',
            timer: null,
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

                var editorHeight = $("#lesson-note-plugin-form .note-content").outerHeight();

                var editor = EditorFactory.create('#note_content', 'simple', {extraFileUploadParams:{group:'course'}, height: editorHeight});
                editor.focus();
                pane.set('editor', editor);
                pane.set('content', editor.html());
                
                $("#lesson-note-plugin-form").on('submit', function() {
                    pane.$('[data-role=saved-message]').html('正在保存').show();
                    editor.sync();
                    var content = editor.html();
                    $.post($(this).attr('action'), $(this).serialize(), function(response) {
                        pane.set('content', content);
                        pane.$('[data-role=saved-message]').html('已保存');
                        setTimeout(function(){
                            pane.$('[data-role=saved-message]').hide();
                        }, 3000);
                    }, 'json').error(function(error) {

                    });
                    return false;
                });

                pane.autosave();
            });
        },

        autosave: function() {
            var pane = this;
            if (pane.get('timer')) {
                clearInterval(pane.get('timer'));
            };

            var timer = setInterval(function() {
                if (pane.get('editor').html() != pane.get('content')) {
                    $("#lesson-note-plugin-form").trigger('submit');
                }
            }, 10000);

            pane.set('timer', timer);
        }
    });

    module.exports = NotePane;

});