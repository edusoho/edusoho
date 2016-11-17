import Emitter from 'es6-event-emitter';

export default class UploaderChooser extends Emitter{
  constructor() {
    super();
    this.element = $('#chooser-upload-panel');
    this._sdk = undefined;
    this._initSdk()
        ._bindEvent();
  }

  get sdk() {
    return undefined;
  }

  reopen() {
    this.destroy();
    this.open()
        ._initSdk()
        ._bindEvent();
  }

  open() {
    let $iframe = $(window.parent.document).find('#task-manage-content-iframe');
    $('.file-chooser-bar').addClass('hidden');
    $('.file-chooser-main').removeClass('hidden');
    $iframe.height($iframe.contents().find('body').height());
    return this;
  }

  _initSdk() {
    if (this._sdk !== undefined) {
      return this;
    }

    let $uploader = $('#uploader-container');
    this._sdk = new UploaderSDK({
      id: $uploader.attr('id'),
      initUrl: $uploader.data('initUrl'),
      finishUrl: $uploader.data('finishUrl'),
      accept: $uploader.data('accept'),
      process: $uploader.data('process'),
      ui:'single'
    });
    return this;
  }

  _bindEvent() {
    this._sdk.on('file.finish', this._onFileUploadFinish.bind(this));
    $('.js-choose-trigger').on('click', this.reopen.bind(this));
    return this;
  }

  _onFileUploadFinish(file) {
    file.source = 'self';

    let placeFileName = (name) => {
      $('[data-role="placeholder"]').html(name);
    };

    this.trigger('select', file);

    let placeMediaAttr = (file) => {
      if (file.length !== 0 && file.length !== undefined) {
        let $minute = $('#minute');
        let $second = $('#second');
        let length = parseInt(file.length);
        let minute = parseInt(length / 60);
        let second = length % 60;
        $minute.val(minute);
        $second.val(second);
        file.minute = minute;
        file.second = second;
      }

      $('[name="media"]').val(JSON.stringify(file));
    };

    placeFileName(file.name);
    placeMediaAttr(file);
    this.destroy();
  }

  destroy() {
    $('.file-chooser-main').addClass('hidden');
    $('.file-chooser-bar').removeClass('hidden');
    let $iframe = $(window.parent.document).find('#task-manage-content-iframe');
    $iframe.height($iframe.contents().find('body').height());

    if(this._sdk === undefined){
      return;
    }

    this._sdk.destroy();
    this._sdk = undefined;
  }
}