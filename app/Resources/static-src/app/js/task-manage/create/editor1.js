import loadAnimation from 'common/load-animation';

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

    this.$contentIframe = $('#task-create-content-iframe');
    this.$finishIframe = $('#task-create-finish-iframe');
    $('#task-create-content-iframe, #task-create-finish-iframe').iFrameResize();

    this.contentData = {};
    this.finishData = {};

    this._init();
    this._initEvent();
  }

  _initEvent() {
    $('#course-tasks-submit').click(event => this._onSave());
    $('#course-tasks-next').click(event => this._onNext(event));
    $('#course-tasks-prev').click(event => this._onPrev(event));

    if (this.mode != 'edit') {
      $('.js-course-tasks-item').click(event => this._onSetType(event));
    }

    window.ltc.on('returnActivity', (msg) => {
      if (!msg.valid) {
        this.contentData = {};
        return;
      }
      this.contentData = msg.data;
      // 第二步的时候，可以下一步，也可以保存，都会触发数据校验
      // 返回数据时，通过标记符，知道下一步还是保存数据
      this.actionType == 'next' ? this._doNext() : this._postData();
    });

    window.ltc.on('getContentData', (msg) => {
      window.ltc.emitChild(msg.iframeId, 'returnContentData', this.contentData);
    });

    window.ltc.on('returnFinishCondition', (msg) => {
      if (!msg.valid) {
        this.finishData = {};
        return;
      }
      this.finishData = msg.data;
      this._postData();
    });
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
      this.actionType = 'next';
      window.ltc.emitChild('task-create-content-iframe', 'getActivity');
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
    $this.siblings().removeClass('active');
    let type = $this.data('type');
    $('[name="mediaType"]').val(type);
    this.taskConfig.contentUrl = $this.data('contentUrl');
    this.taskConfig.finishUrl = $this.data('finishUrl');
    this.type = type;
    this._onNext(event);
  }

  _onSave() {
    if (this.step === 2) {
      this.actionType = 'save';
      window.ltc.emitChild('task-create-content-iframe', 'getActivity');
      return;
    }

    if (this.step === 3) {
      window.ltc.emitChild('task-create-finish-iframe', 'getFinishCondition');
      return;
    }
  }

  _postData() {
    let postData = Object.assign(this._getFormSerializeObject($('#step1-form')), this.contentData, this.finishData);

    $.post(this.taskConfig['saveUrl'], postData)
      .done((response) => {
        this.$element.modal('hide');
        if (response) {
          $('#sortable-list').trigger('addItem', response);
        }
      })
      .fail((response) => {
        $('#course-tasks-submit').attr('disabled', null);
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