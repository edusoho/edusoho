define(function(require, exports, module) {

    require('ckeditor');
    var Widget = require('widget');
    Validator = require('bootstrap.validator');

    var NotePane = Widget.extend({
        attrs: {
            formChanged: false,
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
                CKEDITOR.replace('note_content', {
                    height: '100%',
                    resize_enabled: false,
                    forcePasteAsPlainText: true,
                    toolbar: 'Mini',
                    removePlugins: 'elementspath',
                    filebrowserUploadUrl: '/ckeditor/upload?group=course'
                });
                
                CKEDITOR.instances["note_content"].on("key", function (events, editor) {
                    pane.set('formChanged', true);
                });

                $("#lesson-note-plugin-form").on('submit', function() {
                    CKEDITOR.instances['note_content'].updateElement();
                    $.post($(this).attr('action'), $(this).serialize(), function(response) {
                        pane.set('formChanged', false);

                        pane.$('[data-role=saved-message]').html('最近保存于' + pane._nowTime()).show('slow');
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
                if (pane.get('formChanged')) {
                    $("#lesson-note-plugin-form").trigger('submit');
                }
            }, 20000);
        },

        _nowTime: function () {
            var now = new Date(),
                hours = now.getHours(),
                minutes = now.getMinutes(),
                seconds = now.getSeconds(),

            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            return hours + ':' + minutes + ':' + seconds;
        }

    });

    module.exports = NotePane;

});