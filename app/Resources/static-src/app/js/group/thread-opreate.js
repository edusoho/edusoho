import { initEditor } from './editor';
import notify from 'common/notify';
import AttachmentActions from 'app/js/attachment/widget/attachment-actions';
import Captcha from 'app/common/captcha';

let captcha = new Captcha({drag:{limitType:"groupThread", bar:'#drag-btn', target: '.js-jigsaw'}});

export const initThread = () => {
  let btn = '#post-thread-btn';
  let $form = $('#post-thread-form');
  new AttachmentActions($form);

  if($('#post_content').length) {
    initEditor({
      toolbar: 'Thread',
      replace: 'post_content'
    });
  }

  var captchaProp = null;
  if($("input[name=enable_anti_brush_captcha]").val() == 1){
    captchaProp = {
      captchaClass: captcha,
      isShowCaptcha: $(captcha.params.maskClass).length ? 1 : 0,
    };
  }

  let formValidator = $form.validate({
    currentDom: btn,
    ajax: true,
    captcha: captchaProp,
    rules: {
      'content': {
        required: true,
        minlength: 2,
        maxlength: 3000,
        trim: true
      }
    },
    messages: {
      'content': {
        maxlength: Translator.trans('group.thread.reply.max_length.notice')
      }
    },
    submitSuccess: function (data) {
      console.log(data);
      if (data == '/login') {
        window.location.href = data;
        return;
      }
      // @TODO优化不刷新页面
      window.location.reload();
    },
    submitError: function (data) {
      formValidator.settings.captcha.isShowCaptcha = 1;
      captcha.hideDrag();
    }
  });

  $form.on("submitHandler", function(){
    captcha.setType("groupThread");
  })

  captcha.on('success',function(data){
    if(data.type == 'groupThread'){
      formValidator.settings.captcha.isShowCaptcha = 0;
      $form.find("input[name=_dragCaptchaToken]").val(data.token);
      $form.submit();
    }
  })

  $(btn).click(() => {
    formValidator.form();
  });
};

export const initThreadReplay = () => {
  let $forms = $('.thread-post-reply-form');
  var isShowCaptcha = 0;
  if($("input[name=enable_anti_brush_captcha]").val() == 1){
    isShowCaptcha = 1;
  }

  $forms.each(function () {
    let $form = $(this);
    let content = $form.find('textarea').attr('name');
    let formValidator = $form.validate({
      ignore: '',
      rules: {
        [`${content}`]: {
          required: true,
          minlength: 2,
          maxlength: 3000,
          trim: true
        }
      },
      messages: {
        [`${content}`]: {
          maxlength: Translator.trans('group.thread.reply.max_length.notice'),
        }
      },
      submitHandler: function (form) {
        $(form).triggerHandler("submitHandler");

        if(isShowCaptcha == 1){
          captcha.showDrag();
          return false;
        }

        // @TODO优化全局的submitHandler方法，提交统一方式；
        var _dragCaptchaToken = $(form).find("input[name=_dragCaptchaToken]").val();
        var $replyBtn = $(form).find('.reply-btn');
        var postId = $replyBtn.attr('postId');
        var fromUserIdVal = '';
        if ($('#fromUserId').length > 0) {
          fromUserIdVal = $('#fromUserId').val();
        } else {
          if ($('#fromUserIdNosub').length > 0) {
            fromUserIdVal = $('#fromUserIdNosub').val();
          } else {
            fromUserIdVal = '';
          }
        }
        $replyBtn.button('submiting').addClass('disabled');
        console.log($(form).attr('action'));
        console.log('content=' + $(form).find('textarea').val() + '&' + 'postId=' + postId + '&' + 'fromUserId=' + fromUserIdVal+'_dragCaptchaToken='+_dragCaptchaToken);
        $.ajax({
          url: $(form).attr('action'),
          data: 'content=' + $(form).find('textarea').val() + '&' + 'postId=' + postId + '&' + 'fromUserId=' + fromUserIdVal + '&_dragCaptchaToken='+_dragCaptchaToken,
          cache: false,
          async: false,
          type: 'POST',
          dataType: 'text',
          success: function (url) {
            if (url == '/login') {
              window.location.href = url;
              return;
            }
            // @TODO优化不刷新页面
            window.location.reload();
          },
          error: function (data) {
            isShowCaptcha = 1;
            captcha.hideDrag();
            data = $.parseJSON(data.responseText);
            if (data.error) {
              notify('danger',data.error.message);
            } else {
              notify('danger',Translator.trans('group.post.reply_fail_hint'));
            }
            $replyBtn.button('reset').removeClass('disabled');
          }
        });
      }
    });

    $form.on("submitHandler", function(){
      captcha.setType("groupThreadReply");
    })

    captcha.on('success',function(data){
      if(data.type == 'groupThreadReply'){
        isShowCaptcha = 0;
        $form.find("input[name=_dragCaptchaToken]").val(data.token);
        $form.submit();
      }
    })

    $form.find('button').click((e) => {
      formValidator.form();
    });
  });
};