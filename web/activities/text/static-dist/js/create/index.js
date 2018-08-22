window.ltc.loadCss();
var load = window.ltc.load('jquery', 'validate', 'editor');
load.then(function(){
  var context = window.ltc.getContext();
  var contentCache = '',
    draftId = 0,
    $content = $('#text-content-field'),
    editor,
    validate;

  _init();
  _lanuchAutoSave();


  function _saveDraft() {
    const content = editor.getData();
    const needSave = content !== contentCache;
    if (!needSave) {
      return;
    }

    window.ltc.api({
      name: 'saveCourseDraft',
      data: {
        courseId: context.courseId,
        activityId: context.activityId,
        content:content
      }
    }, function(result) {
      const date = new Date(); //日期对象
      const $title = $('#modal .modal-title', parent.document);
      const now = date.getHours()+'点'+date.getMinutes()+'分'+date.getSeconds()+'秒';
      $title.text('草稿已于'+now+'保存');
      contentCache = content;
    });
  }

  function _lanuchAutoSave() {
    setInterval(function(){
      _saveDraft();
    }, 5000);
  }

  function _init() {
    editor = CKEDITOR.replace('text-content-field', {
      toolbar: 'Task',
      fileSingleSizeLimit: 2,
      filebrowserImageUploadUrl: $content.data('imageUploadUrl'),
      filebrowserFlashUploadUrl: $content.data('flashUploadUrl'),
      allowedContent: true,
      height: 300,
    });
    
    validate = $('#step2-form').validate({
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
  
    editor.on('change', function(){
      $content.val(editor.getData());
      validate.form();
    });
  
    editor.on('blur', function(){
      $content.val(editor.getData());
      validate.form();
    });

    window.ltc.on('getActivity', function(msg){
      if (validate.form()) {
        window.ltc.emit('returnActivity', {valid:true,data:$('#step2-form').serializeObject()});
      }
    });


    if (context.activityId) {
      window.ltc.api({
        name: 'getActivity',
        pathParams: {
          id: context.activityId
        }
      }, function(result) {
        console.log('getActivity');
        console.log(result);
        editor.setData(result['content']);
        $('#title').val(result['title']);
      });
    }
    
    window.ltc.api({
      name : 'getCourseDraft',
      queryParams : {courseId:context.courseId, activityId:context.activityId},
      pathParams : {
        id: draftId
      }
    }, function(result){
      console.log('getCourseDraft');
      console.log(result);
      if (result.content) {
        draftId = result.id;
        $('.js-continue-edit').removeClass('hidden');
        $('.js-continue-edit').on('click', function(){
          const $btn = $(this);
          const content = result.content;
          editor.setData(content);
          $btn.remove();
        });
      }
    });
  }
});