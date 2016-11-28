import Emitter from "es6-event-emitter";

export default class UploaderChooser extends Emitter {
  constructor(element) {
    super();
    this.element = $(element);
    this._sdk = undefined;
    this._initSdk()
        ._bindEvent();
  }

  reopen() {
    this.destroy();
    this.open()
        ._initSdk()
        ._bindEvent();
  }

  open() {
    $('.file-chooser-bar').addClass('hidden');
    $('.file-chooser-main').removeClass('hidden');
    return this;
  }

  _initSdk() {
    if (this._sdk !== undefined) {
      return this;
    }

    let $uploader = this.element.find('#uploader-container');
    this._sdk = new UploaderSDK({
      id: $uploader.attr('id'),
      initUrl: $uploader.data('initUrl'),
      finishUrl: $uploader.data('finishUrl'),
      accept: $uploader.data('accept'),
      process: $uploader.data('process'),
      ui: 'single'
    });
    return this;
  }

  _bindEvent() {
    this._sdk.on('file.finish', this._onFileUploadFinish.bind(this));
    $('.js-choose-trigger').on('click', this.reopen.bind(this));

    this.element.on('change', '.js-upload-params', (event) => {
      let uploadProcess = this.element.find('.js-upload-params').get().reduce((prams, dom) => {
        prams[$(dom).attr('name')] = $(dom).find('option:selected').val();
        return prams;
      }, {});
      this._sdk.setProcess(uploadProcess);
    });

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

    if (this._sdk === undefined) {
      return;
    }

    this._sdk.destroy();
    this._sdk = undefined;
  }
}