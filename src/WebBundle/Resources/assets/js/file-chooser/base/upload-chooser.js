import Chooser from './chooser';

export default class UploaderChooser extends Chooser {
  constructor(element) {
    super();
    this.element = $(element);
    this._sdk = undefined;
    this._initSdk()
        ._bindEvent();
  }

  reopen() {
    this.destroy();
    this._initSdk();
    this._bindEvent();
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

    $('.js-choose-trigger').on('click', this.reopen.bind(this));

    this.element.on('change', '.js-upload-params', (event) => {
      let uploadProcess = this.element.find('.js-upload-params').get().reduce((prams, dom) => {
        prams[$(dom).attr('name')] = $(dom).find('option:selected').val();
        return prams;
      }, {});
      this._sdk.setProcess(uploadProcess);
    });

    this._sdk.on('file.finish', file => this._onFileUploadFinish(file));
    
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
    // this._close();
    if (this._sdk === undefined) {
      return;
    }
    this._sdk.destroy();
    this._sdk = undefined;
  }
}