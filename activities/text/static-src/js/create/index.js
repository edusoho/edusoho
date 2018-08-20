// import Text from './text';
// new Text();

window.ltc.loadCss();
let load = window.ltc.load('jquery', 'validate', 'editor');
load.then(function(){
  let $content = $('#text-content-field');
  let editor = CKEDITOR.replace('text-content-field', {
    toolbar: 'Task',
    fileSingleSizeLimit: 2,
    filebrowserImageUploadUrl: $content.data('imageUploadUrl'),
    filebrowserFlashUploadUrl: $content.data('flashUploadUrl'),
    allowedContent: true,
    height: 300,
  });
  
  let validate = $('#step2-form').validate({
    rules: {
      title: {
        required: true,
        maxlength: 50,
        trim: true,
        course_title: true,
      },
      content: {
        required: true,
        trim: true,
      },
    },
  });

  editor.on('change', () => {
    $content.val(editor.getData());
    validate.form();
  });

  editor.on('blur', () => {
    $content.val(editor.getData());
    validate.form();
  });

  console.log(111234);

  _lanuchAutoSave();

  window.ltc.on('getActivity', (msg) => {
    if (validate.form()) {
      window.ltc.emit('returnActivity', {valid:true,data:$('#step2-form').serializeObject()});
    }
  });


  //接口访问例子
  window.ltc.api({
    "name" : "getCourse",
    "queryParams" : {"type":"course"},
    "pathParams" : {"id":1}
  },(result) => {
    console.log(result);
  })


  function _lanuchAutoSave() {
    console.log(111);
    const $title = $('#modal .modal-title', parent.document);
    this._originTitle = $title.text();
    setInterval(() => {
      _saveDraft();
    }, 5000);
  }

  function _saveDraft() {
    console.log(1111);
    const content = this.editor.getData();
    const needSave = content !== this._contentCache;
    if (!needSave) {
      return;
    }
    const $content = $('[name="content"]');
    $.post($content.data('saveDraftUrl'), { content: content })
      .done(() => {
        const date = new Date(); //日期对象
        const $title = $('#modal .modal-title', parent.document);
        const now = Translator.trans('site.date_format_his', {'hours': date.getHours(), 'minutes': date.getMinutes(), 'seconds': date.getSeconds()});
        $title.text(this._originTitle + Translator.trans('activity.text_manage.save_draft_hint', { createdTime: now }));
        this._contentCache = content;
      });
  }

}).catch(function(e){

});