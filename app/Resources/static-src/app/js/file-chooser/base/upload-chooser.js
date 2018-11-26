import Chooser from './chooser';
import notify from 'common/notify';

export default class UploaderChooser extends Chooser {
  constructor(element) {
    super();
    this.element = $(element);
    this._sdk = undefined;
    this._initSdk()
      ._bindEvent();
  }

  _initSdk() {
    if (this._sdk !== undefined) {
      return this;
    }

    let $uploader = this.element.find('#uploader-container');

    this._sdk = new UploaderSDK({
      id: $uploader.attr('id'),
      sdkBaseUri: app.cloudSdkBaseUri,
      disableDataUpload: app.cloudDisableLogReport,
      disableSentry: app.cloudDisableLogReport,
      initUrl: $uploader.data('initUrl'),
      finishUrl: $uploader.data('finishUrl'),
      accept: $uploader.data('accept'),
      process: this._getUploadProcess(),
      ui: 'single',
      locale: document.documentElement.lang
    });

    return this;
  }

  _bindEvent() {
    this.element.on('change', '.js-upload-params', (event) => {
      this._sdk.setProcess(this._getUploadProcess());
    });

    this._sdk.on('file.finish', file => this._onFileUploadFinish(file));

    this._sdk.on('error', (error) => {
      notify('danger', error.message);
    });

    return this;
  }

  _getUploadProcess() {

    let video = this.element.find('.js-upload-params').get().reduce((prams, dom) => {
      prams[$(dom).attr('name')] = $(dom).find('option:selected').val();
      return prams;
    }, {});

    let uploadProcess = {
      video,
      document: {
        type: 'html',
      },
    };
    const $supportMobileDom = this.element.find('[name=support_mobile]');
    if ($supportMobileDom.length > 0) {
      uploadProcess.common = {
        supportMobile: $supportMobileDom.val(),
      };
    }
    console.log(uploadProcess);
    return uploadProcess;
  }

  _onFileUploadFinish(file) {
    file.source = 'self';

    let placeFileName = (name) => {
      $('[data-role="placeholder"]').html(name);
    };

    this.emit('select', file);

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
  }
}
