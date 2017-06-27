import loadAnimation from 'common/load-animation';
import notify from 'common/notify';
import FileChooser from 'app/js/file-chooser/file-choose';
import SubtitleDialog from 'app/js/activity-manage/video/subtitle/dialog';

class LessonModal {
  constructor(options) {
    this.$element = $(options.element);
    this.$form = $(options.form);
    this.validator();
    this.initfileChooser();
  }

  validator() {
    let validator = this.$form.validate({
      currentDom: '#form-submit',
      ajax: true,
      groups: {
        date: 'minute second'
      },
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          course_title: true,
        },
        minute: 'required unsigned_integer',
        second: 'required second_range',
        'mediaSource': 'required',
      },
      messages: {
        minute: {
          required: '请输入时长',
        },
        second: {
          required: '请输入时长',
          second_range: '秒数只能在0-59之间',
        },
        'mediaSource': "请上传或选择%display%",
      },
      submitSuccess(res) {
        notify('success', Translator.trans('open_course.lesson.create_success'));
        document.location.reload();
      },
      submitError(res) {
        let msg = '';
        let errorRes = JSON.parse(res.responseText);
        if (errorRes.error && errorRes.error.message) {
          msg = errorRes.error.message;
        }
        notify('warning', Translator.trans('open_course.lesson.create_error') + ':' + msg);
      }
    })

    $('#form-submit').click((event) => {
      if(validator.form()) {
        this.$form.submit();
      }
    });

    $(".js-length").blur(function () {
      if (validator && validator.form()) {
        const minute = parseInt($('#minute').val()) | 0;
        const second = parseInt($('#second').val()) | 0;
        $("#length").val(minute * 60 + second);
      }
    });
  }

  initfileChooser() {
    const fileChooser = new FileChooser();
    //字幕组件
    const subtitleDialog = new SubtitleDialog('.js-subtitle-list');
    const onSelectFile = file => {
      FileChooser.closeUI();
      if (file.length && file.length > 0) {
        let minute = parseInt(file.length / 60);
        let second = Math.round(file.length % 60);
        $("#minute").val(minute);
        $("#second").val(second);
        $("#length").val(minute * 60 + second);
      }
      $('#mediaSource').val(file.source);
      if (file.source == 'self') {
        $("#mediaId").val(file.id);
        $("#mediaUri").val('');
      } else {
        $("#mediaUri").val(file.uri);
        $("#mediaId").val(0);
      }
      //渲染字幕
      subtitleDialog.render(file);
    };

    fileChooser.on('select', onSelectFile);
  }
}

// class LessonModal {
//   constructor({ element }) {
//     this.$element = $(element);
//     this.$task_manage_content = this.$element.find('#task-create-content');
    
//     this.iframe_name = 'lesson-create-content-iframe';
//     this.$frame = null;
//     this.$iframe_body = null;
//     this.iframe_jQuery = null;

//     this.lesson_create_form = '#lesson-create-form';

//     this.loaded = false;

//     this.init();
//   }

//   init() {
//     this.initIframe();
//     this.initEvent();
//   }

  
//   initEvent() {
//     this.$element.on('click', '#form-submit', event => this.onSave(event));
//   }

//   onSave(event) {
//     if (!this.validator()) {
//       return;
//     }

//     let $btn = $(event.currentTarget).button('loading');

//     let postData = this.$iframe_body.find(this.lesson_create_form).serializeArray();
//     console.log('postData', postData)
//     $.post(this.$task_manage_content.data('saveUrl'), postData)
//       .done((res) => {
//         notify('success', Translator.trans('open_course.lesson.create_success'));
//         document.location.reload();
//       })
//       .fail((res) => {
//         let msg = '';
//         let errorRes = JSON.parse(res.responseText);
//         if (errorRes.error && errorRes.error.message) {
//           msg = errorRes.error.message;
//         }
//         notify('warning', Translator.trans('open_course.lesson.create_error') + ':' + msg);
//         $btn.button('reset');
//       })
//   }

//   initIframe() {
//     let contentUrl = this.$task_manage_content.data('url');
//     let html = `<iframe class="task-create-content-iframe" id="${this.iframe_name}" name="${this.iframe_name}" scrolling="no" src="${contentUrl}"></iframe>`;

//     this.$task_manage_content.html(html).removeClass('hidden');
//     this.$frame = $(`#${this.iframe_name}`).iFrameResize();
    

//     let loadiframe = () => {
//       this.loaded = true;
//       let validator = {};
//       this.iframe_jQuery = this.$frame[0].contentWindow.$;
//       this.$iframe_body = this.$frame.contents().find('body').addClass('task-iframe-body');
//       this.$iframe_body.find(this.lesson_create_form).data('validator', validator);
//       console.log({'loaded':new Date().toLocaleTimeString()});
//     };

//     this.$frame.load(loadAnimation(loadiframe, this.$task_manage_content));
    
//   }

//   validator() {
//     let $form = this.$iframe_body.find(this.lesson_create_form);
//     let validator = this.iframe_jQuery.data($form[0], 'validator');

//     console.log(validator);

//     if (validator && !validator.form()) {
//       return false;
//     }
    
//     return true;
//   }
// }

new LessonModal({
  element: '#modal',
  form: '#lesson-create-form'
})