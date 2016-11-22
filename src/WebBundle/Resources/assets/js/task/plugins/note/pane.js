class NotePane {
  constructor(option) {
    this.$element = option.element;
    this.$form = this.$element.find('#lesson-note-plugin-form');
    this.$saveMessage = this.$element.find('[data-role=saved-message]');
    this.plugin= option.plugin;
    this.editor=null;
    this.content = '';
    this.timer=null;
  }

  show() {
    let plugin = this.plugin,
    toolbar = plugin.toolbar;
    toolbar.showPane(this.plugin.code);
    //@todo url 
    $.get('http://www.esdev.com/lessonplugin/note/init', {
      courseId: toolbar.courseId,
      lessonId: toolbar.taskId,
    }, (html)=>{
      this.$element.html(html);
      this.editor = CKEDITOR.replace('note_content', {
        toolbar: 'Simple',
        filebrowserImageUploadUrl: this.$element.find('#note_content').data('imageUploadUrl'),
        height: 320
      });
      this.editor.focusManager.focus();
      this.$form.on('submit', (event)=> {
        this.$saveMessage.html(Translator.trans('正在保存')).show();
        this.editor.updateElement();
        this.content = this.editor.getData();
        $.post(this.$form.attr('action'), this.$form.serialize(), function(response) {
          this.$saveMessage.html(Translator.trans('已保存'));
            setTimeout(function(){
              this.$saveMessage.hide();
            }, 3000);
          }, 'json').error(function(error) {
        });
        return false;
      });
      this.autosave();
    });
  }

  autosave() {
    if (this.timer) {
      clearInterval(this.timer);
    }
    let timer = setInterval(()=> {
      if (this.editor.getData() != this.content) {
        this.$form.trigger('submit');
      }
    }, 10000);
    this.timer = timer;
  }
}
export default NotePane;