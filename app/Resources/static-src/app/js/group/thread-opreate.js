import { initEditor } from './editor';

export const initThread = () => {
  let btn = '#post-thread-btn';
  let $form = $("#post-thread-form");
  initEditor({
    toolbar: 'Thread',
    replace: 'post_content'
  });
  let formValidator = $form.validate({
    currentDom: btn,
    rules: {
      'content': {
        required: true,
        minlength: 2,
        visible_character: true
      }
    },
  });
  $(btn).click(()=>{
    if(formValidator.form()) {
      $form.submit();
    }
  })
} 



if ($('#post-thread-form').length > 0) {



  


  

   // submitHandler: function (form) {
    //   if (!$(form).valid()) {
    //     return false;
    //   }
    //   $.ajax({
    //     url: $("#post-thread-form").attr('post-url'),
    //     data: $("#post-thread-form").serialize(),
    //     cache: false,
    //     async: false,
    //     type: "POST",
    //     dataType: 'text',
    //     success: function (url) {
    //       if (url == "/login") {
    //         window.location.href = url;
    //         return;
    //       }
    //       window.location.reload();
    //     },
    //     error: function (data) {
    //       console.log(1);
    //       data = data.responseText;
    //       data = $.parseJSON(data);
    //       if (data.error) {
    //         notify('danger', data.error.message);
    //       } else {
    //         notify('danger', Translator.trans('发表回复失败，请重试'));
    //       }
    //     }
    //   });
    // }

  // var validator_post_content = new Validator({
  //   element: '#post-thread-form',
  //   failSilently: true,
  //   autoSubmit: false,
  //   onFormValidated: function (error) {
  //     if (error) {
  //       return false;
  //     }
  //
  //     $.ajax({
  //       url: $("#post-thread-form").attr('post-url'),
  //       data: $("#post-thread-form").serialize(),
  //       cache: false,
  //       async: false,
  //       type: "POST",
  //       dataType: 'text',
  //       success: function (url) {
  //         if (url == "/login") {
  //           window.location.href = url;
  //           return;
  //         }
  //         window.location.reload();
  //       },
  //       error: function (data) {
  //         console.log(1);
  //         data = data.responseText;
  //         data = $.parseJSON(data);
  //         if (data.error) {
  //           notify('danger', data.error.message);
  //         } else {
  //           notify('danger', Translator.trans('发表回复失败，请重试'));
  //         }
  //       }
  //     });
  //   }
  // });
  // validator_post_content.addItem({
  //   element: '[name="content"]',
  //   required: true,
  //   rule: 'minlength{min:2} visible_character'
  // });

}