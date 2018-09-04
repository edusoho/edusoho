var load = window.ltc.load('bootstrap.css', 'jquery', 'validate', 'editor');
load.then(function(){
  var context = window.ltc.getContext();
  var mediaId = 0;
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
    editor = window.ltc.editor('text-content-field');
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

    window.ltc.on('getValidate', function(msg){
      window.ltc.emit('returnValidate', {valid: validate.form()});
    });

    window.ltc.on('getActivity', function(msg){
      if (validate.form()) {
        if (mediaId !== 0) {
          window.ltc.api({
            name: 'updateActivityResource',
            pathParams: {
              id: mediaId
            },
            data:Object.assign($('#step2-form').serializeObject(), {'resourceType': 'text', 'fromCourseId': context.courseId, 'activityId': context.activityId}),
          }, function (result) {
            window.ltc.emit('returnActivity', {valid:true, data:Object.assign($('#step2-form').serializeObject(), result)});
          });
        } else {
          window.ltc.api({
            name: 'saveActivityResource',
            data:Object.assign($('#step2-form').serializeObject(), {'resourceType': 'text', 'fromCourseId': context.courseId}),
          }, function (result) {
            window.ltc.emit('returnActivity', {valid:true, data:Object.assign($('#step2-form').serializeObject(), result)});
          });
        }
      }
    });

    if (context.activityId) {
      window.ltc.api({
        name: 'getActivity',
        pathParams: {
          id: context.activityId
        }
      }, function(result) {
        $('#title').val(result['title']);
        $content.val(result['content']);
        mediaId = result.mediaId;
        // status的四种状态unloaded, unloaded, ready, destroyed
        // 当status == ready的时候不执行
        editor.on('instanceReady', function( event ){
          editor.setData(result['content'], {
            callback: function() {
              console.log(editor.status);
            }
          });
        });
        // 当status == ready的时候执行
        if (editor.status === 'ready') {
          editor.setData(result['content'], {
            callback: function() {
              console.log(editor.status);
            }
          });
        }
      });
    }
    
    window.ltc.api({
      name : 'getCourseDraft',
      queryParams : {courseId:context.courseId, activityId:context.activityId},
      pathParams : {
        id: draftId
      }
    }, function(result){
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