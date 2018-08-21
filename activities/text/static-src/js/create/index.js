// import Text from './text';
// new Text();

window.ltc.loadCss();
let load = window.ltc.load('jquery', 'validate', 'editor');
load.then(function(){
  let originTitle,contentCache = '';
  let courseId = $('#task-create-content', parent.document).data('courseId');
  let activityId = $('#task-create-content', parent.document).data('activityId');
  let draftId = 0;
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

  _lanuchAutoSave();

  window.ltc.on('getActivity', (msg) => {
    if (validate.form()) {
      window.ltc.emit('returnActivity', {valid:true,data:$('#step2-form').serializeObject()});
    }
  });

  function _lanuchAutoSave() {
    const $title = $('#modal .modal-title', parent.document);
    originTitle = $title.text();
    setInterval(() => {
      _saveDraft();
    }, 5000);
  }
  window.ltc.api({
    "name" : "getCourseDraft",
    "queryParams" : {courseId:courseId,activityId:activityId},
    "pathParams" : {id:draftId}
  }, (result) => {
    if (result.content) {
      draftId = result.id;
      $('.js-continue-edit').removeClass('hidden');
      $('.js-continue-edit').on('click', (event) => {
        const $btn = $(event.currentTarget);
        const content = result.content;
        editor.setData(content);
        $btn.remove();
      });
    }
  });

  function _saveDraft() {
    const content = editor.getData();
    const needSave = content !== contentCache;
    if (!needSave) {
      return;
    }

    window.ltc.api({
      "name": "saveCourseDraft",
      "data": {courseId:courseId,activityId:activityId,content:content}
    }, (result) => {
      const date = new Date(); //日期对象
      const $title = $('#modal .modal-title', parent.document);
      const now = date.getHours()+"点"+date.getMinutes()+"分"+date.getSeconds()+"秒";
      // const now = Translator.trans('site.date_format_his', {'hours': date.getHours(), 'minutes': date.getMinutes(), 'seconds': date.getSeconds()});
      $title.text("草稿已于"+now+"保存");
      contentCache = content;
    })
  }

}).catch(function(e){

});