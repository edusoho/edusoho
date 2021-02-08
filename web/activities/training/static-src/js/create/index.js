import BatchSelect from 'app/common/widget/batch-select';
// import QuestionOperate from 'app/common/component/question-operate';
import Create from './training';
import notify from 'common/notify';

let $from = $('#step2-form');
new Create($('#iframe-content'));
new BatchSelect($from);
// new QuestionOperate($from,$('#attachment-modal',window.parent.document));



var load = window.ltc.load('bootstrap.css', 'jquery', 'validate', 'editor');
load.then(function(){
  var context = window.ltc.getContext();
  var contentCache = '',
    draftId = 0,
    $content = $('#text-content-field'),
    editor,
    validate;

  _init();
  _initDraft();
  _lanuchAutoSave();

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
        lab_type:{
          required:true,
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
      if (!validate.form()) {
        window.ltc.emit('returnActivity', { valid:false });
        return;
      }

      window.ltc.emit('returnActivity', {valid:true, data:$('#step2-form').serializeObject()});
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
    ////////新增
    $("#lab_type").on("change",function(val){
        divShow($(this).val());
    })

    // $("#picker_images_items").on("click",function(){
      // 编辑的时候传递选中id
      // $.get($btn.data('url'), {}, html => {
      //   this.$imagesPickedModal.html(html);
      // });
    // })
  }

  function divShow(id){
    switch(id){
      case "1":
        $('#link_url').rules("add",{
          required:true,
        });
        $(".link_div").show();
        $(".images_div,.dataset_div").hide();
        break;
      case "2":
        $(".link_div").hide();
        $('#link_url').rules("remove");
        $(".images_div,.dataset_div").show();
        break;
      default:
        $(".link_div").hide();
        $(".images_div,.dataset_div").hide();
    }
  }

  

  function _initDraft() {
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
          editor.setData(result.content);
          $(this).remove();
        });
      }
    });
  }

  function _lanuchAutoSave() {
    setInterval(function(){
      _saveDraft();
    }, 5000);
  }

  function _saveDraft() {
    var content = editor.getData();
    var needSave = (content !== contentCache);
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
      var date = new Date(); //日期对象
      var $title = $('#modal .modal-title', parent.document);
      var now = date.getHours()+'点'+date.getMinutes()+'分'+date.getSeconds()+'秒';
      $title.text('草稿已于'+now+'保存');
      contentCache = content;
    });
  }
});