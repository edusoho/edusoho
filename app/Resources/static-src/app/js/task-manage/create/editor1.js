import loadAnimation from 'common/load-animation';
import notify from 'common/notify';

class Editor {
  constructor($modal) {
    this.$element = $modal;
    this.step = 1;
    let $taskType = $('#task-create-type');
    this.taskConfig = {
      type: $taskType.data('editorType'),
      mode: $taskType.data('editorMode'),
      contentUrl: $taskType.data('contentUrl'),
      finishUrl: $taskType.data('finishUrl'),
      saveUrl: $taskType.data('saveUrl'),
    };

    this.$taskType = $taskType;
    this.$taskContent = $('#task-create-content');
    this.$taskFinish = $('#task-create-finish');
    this.$taskSubmit = $('#course-tasks-submit');
    this.$contentIframe = $('#task-create-content-iframe');
    this.$finishIframe = $('#task-create-finish-iframe');
    $('#task-create-content-iframe, #task-create-finish-iframe').iFrameResize();

    this._init();
    this._initEvent();
  }

  _initEvent() {
    this.$taskSubmit.click(event => this._onSave());
    $('#course-tasks-next').click(event => this._onNext(event));
    $('#course-tasks-prev').click(event => this._onPrev(event));

    if (this.taskConfig.mode != 'edit') {
      $('.js-course-tasks-item').click(event => this._onSetType(event));
    } else {
      $('.delete-task').click(event => this._onDelete(event));
    }
  }

  _init() {
    this._inItStep1form();
    if ('edit' == this.taskConfig.mode) {
      //编辑的时候，跳转到第二步
      this._doNext();
    } else {
      //创建时候，渲染第一步页面
      this._switchPage();
    }
  }

  _onNext() {
    if (this.step === 1) {
      this._doNext();
      return;
    }

    if (this.step === 2) {
      window.ltc.emitChild('task-create-content-iframe', 'getValidate');
      window.ltc.once('returnValidate', (msg) => {
        if (msg.valid) {
          this._doNext();
        }
      });

      return;
    }
  }

  _onPrev() {
    if (1 === this.step) {
      return;
    }
    this.step -= 1;
    this._switchPage();
  }

  _doNext() {
    this.step += 1;
    this._switchPage();
    this.$element.trigger('afterNext');
  }

  _onSetType(event) {
    let $this = $(event.currentTarget).addClass('active');
    let type = $this.data('type');
    if (this.type != type) {
      $this.siblings().removeClass('active');
      this.$finishIframe.attr('src', '');
      $('[name="mediaType"]').val(type);
      this.taskConfig.contentUrl = $this.data('contentUrl');
      this.taskConfig.finishUrl = $this.data('finishUrl');
      this.type = type;
    }

    this._onNext(event);
  }

  async getActivityFinishCondition() {
    let self = this;
    return new Promise((resolve,reject) => {
      if (!self.$finishIframe.attr('src')) {
        resolve({});
      }

      window.ltc.emitChild('task-create-finish-iframe', 'getCondition');
      window.ltc.once('returnCondition', (msg) => {
        if (!msg.valid) {
          reject();
          return;
        }

        resolve(msg.data);
      });  
    });
  }

  async getActivityContent() {
    let self = this;
    return new Promise((resolve,reject) => {
      window.ltc.emitChild('task-create-content-iframe', 'getActivity');
      window.ltc.once('returnActivity', (msg) => {
        if (!msg.valid) {
          reject();
          return;
        }

        resolve(msg.data);
      });      
    }); 
  }

  async _onSave() {
    this.$taskSubmit.attr('disabled', true);
    let content;
    await this.getActivityContent().then((data) => {
      content = data;
      return this.getActivityFinishCondition();
    }).then((condition) => {
      this.$taskSubmit.button('loading');
      let postData = Object.assign(this._getFormSerializeObject($('#step1-form')), content, condition);
      $.post(this.taskConfig['saveUrl'], postData)
        .done((response) => {
          this.$element.modal('hide');
          if (response) {
            $('#sortable-list').trigger('addItem', response);
          }
        })
        .fail((response) => {
          this.$taskSubmit.button('reset');
        });
    }).catch(() => {
      this.$taskSubmit.attr('disabled', false);
      this.$taskSubmit.button('reset');
    })
  }

  _onDelete(event) {
    let $btn = $(event.currentTarget);
    let url = $btn.data('url');
    if (url === undefined) {
      return;
    }
    if (!confirm(Translator.trans(Translator.trans('task_manage.delete_hint')))) {
      return;
    }
    $.post(url)
      .then((response) => {
        notify('success', Translator.trans('task_manage.delete_success_hint'));
        this.$element.modal('hide');


        document.location.reload();
      })
      .fail(error => {
        notify('warning', Translator.trans('task_manage.delete_failed_hint'));
      });
  }

  _switchPage() {
    this._renderStep();
    this._renderContent();

    if (1 == this.step) {
      this._rendButton(1);
    }
    if (2 == this.step) {
      this._initContentIframe();
    }
    if (3 == this.step) {
      this._initFinishIframe();
    }
  }

  _initContentIframe() {
    if (!this.taskConfig.contentUrl) {
      return;
    }

    if (this.$contentIframe.attr('src') != this.taskConfig.contentUrl) {
      this.$contentIframe.hide();
      this.$contentIframe.attr('src', this.taskConfig.contentUrl);
      this.$contentIframe.load(loadAnimation(() => {
        this._rendButton(2);
      }, this.$taskContent));
    } else {
      this._rendButton(2);
    }
  }

  _initFinishIframe() {
    if (!this.taskConfig.finishUrl) {
      return;
    }
    if (this.$finishIframe.attr('src') != this.taskConfig.finishUrl) {
      this.$finishIframe.hide();
      this.$finishIframe.attr('src', this.taskConfig.finishUrl);
      this.$finishIframe.load(loadAnimation(() => {
        this._sendContent();
        this._rendButton(3);
      }, this.$taskFinish));
    } else {
      this._sendContent();
      this._rendButton(3);
    }
  }

  _sendContent() {
    window.ltc.once('returnValidate',  (msg) => {
      window.ltc.emitChild('task-create-finish-iframe', 'getContent', msg);
    });
    window.ltc.emitChild('task-create-content-iframe', 'getValidate');
  }

  _inItStep1form() {
    let $step1_form = $('#step1-form');
    let validator = $step1_form.validate({
      rules: {
        mediaType: {
          required: true,
        },
      },
      messages: {
        mediaType: Translator.trans('validate.choose_item.message'),
      }
    });
    $step1_form.data('validator', validator);
  }

  _rendButton(step) {
    if (1 === step) {
      this.$element.find('.modal-footer').children().addClass('hidden');
    }
    if (2=== step) {
      if (this.taskConfig.mode === 'edit') {

        this.$element.find('#course-tasks-prev').addClass('hidden').siblings().removeClass('hidden');
      } else {
        this.$element.find('.modal-footer').children().removeClass('hidden');
      }
    }

    if (3 === step) {
      this.$element.find('#course-tasks-next').addClass('hidden').siblings().removeClass('hidden');
    }
  }

  _renderStep() {
    if (!this.$setp) {
      this.$step = $('#task-create-step');
    }
    let $currentSetp = this.$step.find('li').eq(this.step - 1);
    $currentSetp.addClass('doing').prev().addClass('done').removeClass('doing');
    $currentSetp.next().removeClass('doing').removeClass('done');
  }

  _renderContent() {
    let content = {
      1: this.$taskType,
      2: this.$taskContent,
      3: this.$taskFinish,
    };
    if (content[this.step]) {
      content[this.step].removeClass('hidden').siblings('div').addClass('hidden');
    }
  }

  _getFormSerializeObject($e) {
    let o = {};
    let a = $e.serializeArray();
    $.each(a, function() {
      if (o[this.name]) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || '');
      } else {
        o[this.name] = this.value || '';
      }
    });

    return o;
  }
}

export default Editor;