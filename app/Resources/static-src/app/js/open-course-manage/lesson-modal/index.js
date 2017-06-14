import loadAnimation from 'common/load-animation';
import notify from 'common/notify';

class LessonModal {
  constructor({ element }) {
    this.$element = $(element);
    this.$task_manage_content = this.$element.find('#task-create-content');
    
    this.iframe_name = 'lesson-create-content-iframe';
    this.$frame = null;
    this.$iframe_body = null;
    this.iframe_jQuery = null;

    this.lesson_create_form = '#lesson-create-form';

    this.loaded = false;

    this.init();
  }

  init() {
    this.initIframe();
    this.initEvent();
  }

  
  initEvent() {
    this.$element.on('click', '#form-submit', event => this.onSave(event));
  }

  onSave(event) {
    if (!this.validator()) {
      return;
    }

    let $btn = $(event.currentTarget).button('loading');

    let postData = this.$iframe_body.find(this.lesson_create_form).serializeArray();
    console.log('postData', postData)
    $.post(this.$task_manage_content.data('saveUrl'), postData)
      .done((res) => {
        notify('success', '创建课时成功');
        document.location.reload();
      })
      .fail((res) => {
        let msg = '';
        let errorRes = JSON.parse(res.responseText);
        if (errorRes.error && errorRes.error.message) {
          msg = errorRes.error.message;
        }
        notify('warning', '创建课时出错: ' + msg);
        $btn.button('reset');
      })
  }

  initIframe() {
    let contentUrl = this.$task_manage_content.data('url');
    let html = `<iframe class="task-create-content-iframe" id="${this.iframe_name}" name="${this.iframe_name}" scrolling="no" src="${contentUrl}"></iframe>`;

    this.$task_manage_content.html(html).removeClass('hidden');
    this.$frame = $(`#${this.iframe_name}`).iFrameResize();
    

    let loadiframe = () => {
      this.loaded = true;
      let validator = {};
      this.iframe_jQuery = this.$frame[0].contentWindow.$;
      this.$iframe_body = this.$frame.contents().find('body').addClass('task-iframe-body');
      this.$iframe_body.find(this.lesson_create_form).data('validator', validator);
      console.log({'loaded':new Date().toLocaleTimeString()});
    };

    this.$frame.load(loadAnimation(loadiframe, this.$task_manage_content));
    
  }

  validator() {
    let $from = this.$iframe_body.find(this.lesson_create_form);
    let validator = this.iframe_jQuery.data($from[0], 'validator');

    console.log(validator);

    if (validator && !validator.form()) {
      return false;
    }
    
    return true;
  }
}

new LessonModal({
  element: '#modal'
})