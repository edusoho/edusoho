import { initEditor } from './editor';
import notify from 'common/notify';

export const initThread = () => {
  let btn = '#post-thread-btn';
  let $form = $("#post-thread-form");
  initEditor({
    toolbar: 'Thread',
    replace: 'post_content'
  });
  let formValidator = $form.validate({
    currentDom: btn,
    ajax: true,
    rules: {
      'content': {
        required: true,
        minlength: 2,
        visible_character: true
      }
    },
    submitError() {
      data = data.responseText;
      data = $.parseJSON(data);
      if (data.error) {
        notify('danger', data.error.message);
      } else {
        notify('danger', Translator.trans('发表回复失败，请重试'));
      }
    },
    submitSuccess: function (data) {
      console.log(data);
      // @TODO优化不刷新页面
      if (data == "/login") {
        window.location.href = url;
        return;
      }
      // window.location.reload();
    },
  });
  console.log(formValidator);
  $(btn).click(() => {
    if (formValidator.form()) {
      console.log('submit');
      $form.submit();
    }
  })
}

export const initThreadReplay = () => {
  let $forms = $('.thread-post-reply-form');
  $forms.each(function() {
    let $form = $(this);
    let content = $form.find('textarea').attr('name');
    let formValidator = $form.validate({
      ignore:'',
      rules: {
        [`${content}`]: {
          required: true,
          minlength: 2,
          visible_character: true
        }
      },
      submitError() {
        console.log('submitError');
      },
      submitSuccess: function (data) {
        console.log('submitSuccess');
      },
    });

    console.log(formValidator);
    $form.find('button').click(()=>{
      if(formValidator.form()) {
        $form.submit();
      }
    })
  })
}