class NotePane {
  constructor(option) {
    this.$element = option.element;
    this.editor=null;
    this.content = '';
    this.timer=null;
    this.plugin= option.plugin;
  }

  show() {
    let toolbar = this.plugin.toolbar;
    this.plugin.toolbar.showPane(this.plugin.code);
    console.log(this.plugin.api.init);
    $.get('http://www.esdev.com/lessonplugin/note/init', {
      courseId: toolbar.courseId,
      lessonId: toolbar.taskId,
    }, (html)=>{
      this.$element.html(html);
      var editorHeight = $("#lesson-note-plugin-form .note-content").height() - 50;
      var editor = CKEDITOR.replace('note_content', {
        toolbar: 'Simple',
        filebrowserImageUploadUrl: $('#note_content').data('imageUploadUrl'),
        height: editorHeight
      });
      editor.focusManager.focus();
      pane.set('editor', editor);
      pane.set('content', editor.getData());
      $("#lesson-note-plugin-form").on('submit', function() {
        pane.$('[data-role=saved-message]').html(Translator.trans('正在保存')).show();
        editor.updateElement();
        var content = editor.getData();
        $.post($(this).attr('action'), $(this).serialize(), function(response) {
            pane.set('content', content);
            pane.$('[data-role=saved-message]').html(Translator.trans('已保存'));
            setTimeout(function(){
                pane.$('[data-role=saved-message]').hide();
            }, 3000);
        }, 'json').error(function(error) {

        });
        return false;
      });
      this.autosave();
    });
  }

  autosave() {
    console.log('save');
  }
}
export default NotePane;